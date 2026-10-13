<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected TaxService $taxService
    ) {}

    /**
     * Procesa y registra una venta completa con cálculo tributario centralizado (IGV Perú),
     * idempotencia contra doble envío, control de concurrencia y transaccionalidad atómica.
     *
     * @param User $user Usuario vendedor o administrador autenticado
     * @param array $data Datos de la venta (customer_id, payment_method, notes, items, idempotency_key, prices_include_tax)
     * @return Sale
     * @throws DomainException|InvalidArgumentException
     */
    public function createSale(User $user, array $data): Sale
    {
        $itemsData = $data['items'] ?? [];
        if (empty($itemsData) || !is_array($itemsData)) {
            throw new InvalidArgumentException('La venta debe incluir al menos un producto.');
        }

        // Control de idempotencia ante reintentos de red o doble clic en POS
        $idempotencyKey = !empty($data['idempotency_key']) ? trim((string) $data['idempotency_key']) : null;
        if ($idempotencyKey !== null) {
            $existingSale = Sale::where('idempotency_key', $idempotencyKey)
                ->with(['items.product', 'customer', 'user'])
                ->first();
            if ($existingSale) {
                return $existingSale;
            }
        }

        return DB::transaction(function () use ($user, $data, $itemsData, $idempotencyKey) {
            // Re-verificar idempotencia dentro de la transacción
            if ($idempotencyKey !== null) {
                $existingInTx = Sale::where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->with(['items.product', 'customer', 'user'])
                    ->first();
                if ($existingInTx) {
                    return $existingInTx;
                }
            }

            // 1. Determinar datos del cliente
            $customerId = $data['customer_id'] ?? null;
            $customerName = 'Cliente Ocasional';

            if (!empty($customerId)) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $customerName = $customer->name;
                } else {
                    $customerId = null;
                }
            } elseif (!empty($data['customer_name'])) {
                $customerName = trim($data['customer_name']);
            }

            // 2. Extraer y consolidar cantidades y descuentos por producto
            $aggregatedItems = [];
            foreach ($itemsData as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);
                $discount = isset($item['discount_amount']) ? max(0.00, (float) $item['discount_amount']) : 0.00;

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Cada línea de venta debe tener un producto válido y cantidad mayor a cero.');
                }

                if (!isset($aggregatedItems[$productId])) {
                    $aggregatedItems[$productId] = [
                        'quantity' => 0,
                        'discount_amount' => 0.00,
                    ];
                }
                $aggregatedItems[$productId]['quantity'] += $qty;
                $aggregatedItems[$productId]['discount_amount'] = round($aggregatedItems[$productId]['discount_amount'] + $discount, 2);
            }

            // 3. Bloqueo pesimista ordenado por ID para evitar deadlocks en concurrencia
            $productIds = array_keys($aggregatedItems);
            sort($productIds);

            $lockedProducts = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 4. Validar existencia, estado y disponibilidad en el servidor
            foreach ($aggregatedItems as $productId => $agg) {
                $qty = $agg['quantity'];

                if (!$lockedProducts->has($productId)) {
                    throw new DomainException("El producto con ID #{$productId} no existe en el catálogo.");
                }

                /** @var Product $product */
                $product = $lockedProducts->get($productId);

                if (!$product->is_active) {
                    throw new DomainException("El producto '{$product->name}' (SKU: {$product->sku}) está inactivo y no puede venderse.");
                }

                if ($product->stock < $qty) {
                    throw new DomainException("Stock insuficiente para '{$product->name}' (SKU: {$product->sku}). Disponible: {$product->stock}, Solicitado: {$qty}.");
                }
            }

            // 5. Cálculo tributario centralizado en servidor usando exclusivamente precios de la BD
            $pricesIncludeTax = isset($data['prices_include_tax'])
                ? (bool) $data['prices_include_tax']
                : (bool) config('erp.prices_include_igv', true);

            $preparedItems = [];
            $calculatedLines = [];

            foreach ($aggregatedItems as $productId => $agg) {
                /** @var Product $product */
                $product = $lockedProducts->get($productId);
                $qty = $agg['quantity'];
                $discount = $agg['discount_amount'];

                $taxCalc = $this->taxService->calculateLine(
                    catalogUnitAmount: (float) $product->sale_price,
                    quantity: $qty,
                    taxType: $product->tax_type ?? 'gravado',
                    pricesIncludeTax: $pricesIncludeTax,
                    discountAmount: $discount
                );

                $calculatedLines[] = $taxCalc;
                $preparedItems[] = [
                    'product' => $product,
                    'cost_price' => (float) $product->cost_price,
                    'calc' => $taxCalc,
                ];
            }

            $totals = $this->taxService->summarizeTotals($calculatedLines);

            // 6. Generar número consecutivo de venta único
            $createdAt = isset($data['created_at']) ? Carbon::parse($data['created_at']) : Carbon::now();
            $period = $createdAt->format('Ym');
            $latestSale = Sale::where('sale_number', 'LIKE', "VEN-{$period}-%")
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestSale && preg_match("/VEN-{$period}-(\d+)/", $latestSale->sale_number, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }
            $saleNumber = sprintf("VEN-%s-%04d", $period, $nextSequence);

            // 7. Crear encabezado de la venta con desglose tributario
            $sale = new Sale([
                'sale_number' => $saleNumber,
                'idempotency_key' => $idempotencyKey,
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'currency' => config('erp.currency_code', 'PEN'),
                'prices_include_tax' => $pricesIncludeTax,
                'taxable_amount' => $totals['taxable_amount'],
                'exempt_amount' => $totals['exempt_amount'],
                'unaffected_amount' => $totals['unaffected_amount'],
                'discount_total' => $totals['discount_total'],
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'] ?? 'efectivo',
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['created_at'])) {
                $sale->created_at = $createdAt;
                $sale->updated_at = $createdAt;
            }
            $sale->save();

            // 8. Crear líneas con instantánea histórica de impuestos y descontar inventario
            foreach ($preparedItems as $prep) {
                $calc = $prep['calc'];
                $saleItem = new SaleItem([
                    'sale_id' => $sale->id,
                    'product_id' => $prep['product']->id,
                    'product_name' => $prep['product']->name,
                    'product_sku' => $prep['product']->sku,
                    'tax_type' => $calc['tax_type'],
                    'tax_rate' => $calc['tax_rate'],
                    'quantity' => $calc['quantity'],
                    'unit_value' => $calc['unit_value'],
                    'unit_price' => $calc['unit_price'],
                    'cost_price' => $prep['cost_price'],
                    'discount_amount' => $calc['discount_amount'],
                    'tax_base' => $calc['tax_base'],
                    'tax_amount' => $calc['tax_amount'],
                    'subtotal' => $calc['subtotal'],
                    'line_total' => $calc['line_total'],
                ]);

                if (isset($data['created_at'])) {
                    $saleItem->created_at = $createdAt;
                    $saleItem->updated_at = $createdAt;
                }
                $saleItem->save();

                // Descontar inventario a través de InventoryService
                $this->inventoryService->recordMovement(
                    product: $prep['product'],
                    user: $user,
                    type: 'out',
                    quantity: $calc['quantity'],
                    reason: "Venta #{$sale->sale_number}",
                    referenceType: 'Sale',
                    referenceId: $sale->id
                );
            }

            return $sale->load(['items.product', 'customer', 'user']);
        });
    }

    /**
     * Anula internamente una operación de venta y revierte el stock al almacén
     * garantizando que nunca pueda revertirse dos veces.
     *
     * Nota de dominio: Esta es una anulación comercial interna en PymeFlow ERP
     * y no equivale a una Nota de Crédito Electrónica ni comunicación de baja ante SUNAT.
     */
    public function cancelSale(Sale $sale, User $user, string $reason): Sale
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('Debe indicar el motivo de la anulación o devolución.');
        }

        return DB::transaction(function () use ($sale, $user, $reason) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::whereKey($sale->id)
                ->lockForUpdate()
                ->with('items.product')
                ->firstOrFail();

            if ($lockedSale->status === 'cancelled') {
                throw new DomainException("La venta #{$lockedSale->sale_number} ya se encuentra anulada.");
            }

            // Protección adicional en Kardex contra duplicación de reversa
            $alreadyReversed = InventoryMovement::where('reference_type', 'SaleCancellation')
                ->where('reference_id', $lockedSale->id)
                ->exists();

            if ($alreadyReversed) {
                throw new DomainException("El stock de la venta #{$lockedSale->sale_number} ya fue revertido previamente.");
            }

            // Revertir stock de cada línea de la venta
            foreach ($lockedSale->items as $item) {
                $product = $item->product ?? Product::findOrFail($item->product_id);

                $this->inventoryService->recordMovement(
                    product: $product,
                    user: $user,
                    type: 'in',
                    quantity: (int) $item->quantity,
                    reason: "Anulación Venta #{$lockedSale->sale_number}: {$reason}",
                    referenceType: 'SaleCancellation',
                    referenceId: $lockedSale->id
                );
            }

            $lockedSale->status = 'cancelled';
            $lockedSale->cancelled_at = Carbon::now();
            $lockedSale->cancelled_by = $user->id;
            $lockedSale->cancellation_reason = $reason;
            $lockedSale->save();

            return $lockedSale->load(['items.product', 'customer', 'user', 'cancelledByUser']);
        });
    }
}
