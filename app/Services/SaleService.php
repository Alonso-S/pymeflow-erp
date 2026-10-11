<?php

namespace App\Services;

use App\Models\Customer;
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
        protected InventoryService $inventoryService
    ) {}

    /**
     * Procesa y registra una venta completa con control de concurrencia y transaccionalidad.
     *
     * @param User $user Usuario vendedor o administrador autenticado
     * @param array $data Datos de la venta (customer_id, payment_method, notes, items)
     * @return Sale
     * @throws DomainException|InvalidArgumentException
     */
    public function createSale(User $user, array $data): Sale
    {
        $itemsData = $data['items'] ?? [];
        if (empty($itemsData) || !is_array($itemsData)) {
            throw new InvalidArgumentException('La venta debe incluir al menos un producto.');
        }

        return DB::transaction(function () use ($user, $data, $itemsData) {
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

            // 2. Extraer y agrupar IDs de productos
            $quantitiesByProduct = [];
            foreach ($itemsData as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Cada línea de venta debe tener un producto válido y cantidad mayor a cero.');
                }

                $quantitiesByProduct[$productId] = ($quantitiesByProduct[$productId] ?? 0) + $qty;
            }

            // 3. Bloqueo pesimista contra condiciones de carrera concurrentes
            $productIds = array_keys($quantitiesByProduct);
            $lockedProducts = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 4. Validar existencia, estado y disponibilidad en el servidor
            foreach ($quantitiesByProduct as $productId => $qty) {
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

            // 5. Cálculo estricto en servidor de subtotales y total usando precios de la base de datos
            $subtotalSum = 0.0;
            $preparedItems = [];

            foreach ($quantitiesByProduct as $productId => $qty) {
                $product = $lockedProducts->get($productId);

                $unitPrice = (float) $product->sale_price;
                $costPrice = (float) $product->cost_price;
                $lineSubtotal = round($unitPrice * $qty, 2);

                $subtotalSum += $lineSubtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'cost_price' => $costPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $subtotalSum = round($subtotalSum, 2);
            $tax = 0.00; // Impuestos pueden calcularse si aplica
            $total = round($subtotalSum + $tax, 2);

            // 6. Generar número consecutivo de venta único
            $period = Carbon::now()->format('Ym');
            $latestSale = Sale::where('sale_number', 'LIKE', "VEN-{$period}-%")
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestSale && preg_match("/VEN-{$period}-(\d+)/", $latestSale->sale_number, $matches)) {
                $nextSequence = ((int) $matches[1]) + 1;
            }
            $saleNumber = sprintf("VEN-%s-%04d", $period, $nextSequence);

            // 7. Crear encabezado de la venta
            $sale = Sale::create([
                'sale_number' => $saleNumber,
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'subtotal' => $subtotalSum,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? 'efectivo',
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // 8. Crear líneas de venta y descontar inventario con auditoría
            foreach ($preparedItems as $prep) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $prep['product']->id,
                    'product_name' => $prep['product']->name,
                    'product_sku' => $prep['product']->sku,
                    'quantity' => $prep['quantity'],
                    'unit_price' => $prep['unit_price'],
                    'cost_price' => $prep['cost_price'],
                    'subtotal' => $prep['subtotal'],
                ]);

                // Descontar inventario a través de InventoryService
                $this->inventoryService->recordMovement(
                    product: $prep['product'],
                    user: $user,
                    type: 'out',
                    quantity: $prep['quantity'],
                    reason: "Venta #{$sale->sale_number}",
                    referenceType: 'Sale',
                    referenceId: $sale->id
                );
            }

            return $sale->load(['items.product', 'customer', 'user']);
        });
    }
}
