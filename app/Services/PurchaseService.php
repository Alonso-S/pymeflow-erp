<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected TaxService $taxService
    ) {}

    /**
     * Registra una Orden de Compra (o compra con recepción inmediata confirmada).
     *
     * Nota de dominio: Una orden en estado 'ordered' NO incrementa el stock hasta
     * que se confirme su recepción física mediante receivePurchase().
     */
    public function createPurchase(User $user, array $data): Purchase
    {
        $itemsData = $data['items'] ?? [];
        if (empty($itemsData) || !is_array($itemsData)) {
            throw new InvalidArgumentException('La orden de compra debe incluir al menos un producto.');
        }

        return DB::transaction(function () use ($user, $data, $itemsData) {
            $supplierId = (int) ($data['supplier_id'] ?? 0);
            $supplier = Supplier::find($supplierId);

            if (!$supplier || !$supplier->is_active) {
                throw new DomainException('El proveedor seleccionado no existe o se encuentra inactivo.');
            }

            $aggregatedItems = [];
            foreach ($itemsData as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);
                $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : null;

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Cada ítem de compra debe tener un producto válido y cantidad mayor a cero.');
                }
                if ($unitCost !== null && $unitCost < 0) {
                    throw new InvalidArgumentException('El costo unitario no puede ser negativo.');
                }

                if (!isset($aggregatedItems[$productId])) {
                    $aggregatedItems[$productId] = [
                        'quantity' => 0,
                        'unit_cost' => $unitCost,
                    ];
                }
                $aggregatedItems[$productId]['quantity'] += $qty;
                if ($unitCost !== null) {
                    $aggregatedItems[$productId]['unit_cost'] = $unitCost;
                }
            }

            $productIds = array_keys($aggregatedItems);
            sort($productIds);

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            $pricesIncludeTax = isset($data['prices_include_tax'])
                ? (bool) $data['prices_include_tax']
                : false;

            $preparedItems = [];
            $calculatedLines = [];

            foreach ($aggregatedItems as $productId => $agg) {
                if (!$products->has($productId)) {
                    throw new DomainException("El producto con ID #{$productId} no existe.");
                }

                /** @var Product $product */
                $product = $products->get($productId);
                $unitCost = $agg['unit_cost'] !== null ? (float) $agg['unit_cost'] : (float) $product->cost_price;

                $calc = $this->taxService->calculateLine(
                    catalogUnitAmount: $unitCost,
                    quantity: $agg['quantity'],
                    taxType: $product->tax_type ?? 'gravado',
                    pricesIncludeTax: $pricesIncludeTax
                );

                $calculatedLines[] = $calc;
                $preparedItems[] = [
                    'product' => $product,
                    'calc' => $calc,
                ];
            }

            $totals = $this->taxService->summarizeTotals($calculatedLines);

            $period = Carbon::now()->format('Ym');
            $latestPurchase = Purchase::where('purchase_number', 'LIKE', "COM-{$period}-%")
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextSeq = 1;
            if ($latestPurchase && preg_match("/COM-{$period}-(\d+)/", $latestPurchase->purchase_number, $matches)) {
                $nextSeq = ((int) $matches[1]) + 1;
            }
            $purchaseNumber = sprintf("COM-%s-%04d", $period, $nextSeq);

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $supplier->id,
                'user_id' => $user->id,
                'supplier_document' => !empty($data['supplier_document']) ? trim($data['supplier_document']) : null,
                'currency' => config('erp.currency_code', 'PEN'),
                'prices_include_tax' => $pricesIncludeTax,
                'taxable_amount' => $totals['taxable_amount'],
                'exempt_amount' => $totals['exempt_amount'],
                'unaffected_amount' => $totals['unaffected_amount'],
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'status' => 'ordered',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($preparedItems as $prep) {
                $calc = $prep['calc'];
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $prep['product']->id,
                    'product_name' => $prep['product']->name,
                    'product_sku' => $prep['product']->sku,
                    'tax_type' => $calc['tax_type'],
                    'tax_rate' => $calc['tax_rate'],
                    'quantity' => $calc['quantity'],
                    'unit_cost' => $calc['unit_value'],
                    'tax_base' => $calc['tax_base'],
                    'tax_amount' => $calc['tax_amount'],
                    'line_total' => $calc['line_total'],
                ]);
            }

            // Si el usuario indicó recepción inmediata en el formulario
            if (!empty($data['receive_immediately'])) {
                return $this->receivePurchase($purchase, $user);
            }

            return $purchase->load(['supplier', 'user', 'items.product']);
        });
    }

    /**
     * Confirma la recepción física de una orden de compra pendiente ('ordered'),
     * ingresando las existencias en almacén, actualizando el costo del producto
     * y evitando recepciones duplicadas.
     */
    public function receivePurchase(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase, $user) {
            /** @var Purchase $lockedPurchase */
            $lockedPurchase = Purchase::whereKey($purchase->id)
                ->lockForUpdate()
                ->with('items.product')
                ->firstOrFail();

            if ($lockedPurchase->status === 'received') {
                throw new DomainException("La orden de compra #{$lockedPurchase->purchase_number} ya fue recepcionada en almacén.");
            }

            if ($lockedPurchase->status === 'cancelled') {
                throw new DomainException("No se puede recepcionar una orden de compra anulada (#{$lockedPurchase->purchase_number}).");
            }

            $alreadyReceived = InventoryMovement::where('reference_type', 'Purchase')
                ->where('reference_id', $lockedPurchase->id)
                ->exists();

            if ($alreadyReceived) {
                throw new DomainException("Los movimientos de inventario para la compra #{$lockedPurchase->purchase_number} ya fueron registrados.");
            }

            foreach ($lockedPurchase->items as $item) {
                /** @var Product $product */
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();

                // Actualizar costo referencial del producto con el valor unitario de compra
                if ((float) $item->unit_cost > 0) {
                    $product->cost_price = round((float) $item->unit_cost, 2);
                    $product->save();
                }

                $this->inventoryService->recordMovement(
                    product: $product,
                    user: $user,
                    type: 'in',
                    quantity: (int) $item->quantity,
                    reason: "Recepción Compra #{$lockedPurchase->purchase_number}" . ($lockedPurchase->supplier_document ? " (Doc: {$lockedPurchase->supplier_document})" : ''),
                    referenceType: 'Purchase',
                    referenceId: $lockedPurchase->id
                );
            }

            $lockedPurchase->status = 'received';
            $lockedPurchase->received_at = Carbon::now();
            $lockedPurchase->received_by = $user->id;
            $lockedPurchase->save();

            return $lockedPurchase->load(['supplier', 'user', 'receivedByUser', 'items.product']);
        });
    }

    /**
     * Cancela una orden de compra pendiente que aún no ha ingresado a almacén.
     */
    public function cancelPurchase(Purchase $purchase, User $user): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            /** @var Purchase $lockedPurchase */
            $lockedPurchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            if ($lockedPurchase->status !== 'ordered') {
                throw new DomainException("Solo se pueden cancelar órdenes de compra pendientes de recepción (estado actual: {$lockedPurchase->status}).");
            }

            $lockedPurchase->status = 'cancelled';
            $lockedPurchase->save();

            return $lockedPurchase->load(['supplier', 'user', 'items.product']);
        });
    }
}
