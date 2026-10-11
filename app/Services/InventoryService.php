<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Registra un movimiento inmutable en el historial y actualiza el stock del producto.
     *
     * @param Product $product
     * @param User $user
     * @param string $type in|out|adjustment
     * @param int $quantity Cantidad positiva a ingresar o retirar, o nueva cantidad en ajuste
     * @param string $reason Motivo justificado del movimiento
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return InventoryMovement
     * @throws DomainException
     */
    public function recordMovement(
        Product $product,
        User $user,
        string $type,
        int $quantity,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryMovement {
        if ($quantity <= 0 && $type !== 'adjustment') {
            throw new DomainException('La cantidad del movimiento debe ser un número entero mayor a cero.');
        }

        $previousStock = $product->stock;

        switch ($type) {
            case 'in':
                $newStock = $previousStock + $quantity;
                break;

            case 'out':
                if ($previousStock < $quantity) {
                    throw new DomainException("Stock insuficiente para el producto '{$product->name}' (SKU: {$product->sku}). Disponible: {$previousStock}, Solicitado: {$quantity}.");
                }
                $newStock = $previousStock - $quantity;
                break;

            case 'adjustment':
                if ($quantity < 0) {
                    throw new DomainException('El stock ajustado no puede ser un número negativo.');
                }
                $newStock = $quantity;
                // La cantidad registrada en el movimiento refleja la diferencia absoluta
                $quantity = abs($newStock - $previousStock);
                break;

            default:
                throw new DomainException("Tipo de movimiento inválido: {$type}. Use 'in', 'out' o 'adjustment'.");
        }

        // Persistir nuevo stock en el producto
        $product->stock = $newStock;
        $product->save();

        // Registrar auditoría inmutable
        return InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
