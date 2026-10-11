<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Administradores y vendedores pueden registrar ventas
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['required', 'string', 'in:efectivo,tarjeta,transferencia'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Seleccione un método de pago válido.',
            'payment_method.in' => 'El método de pago debe ser efectivo, tarjeta o transferencia.',
            'items.required' => 'La venta debe contener al menos un producto.',
            'items.min' => 'Debe agregar al menos un producto al carrito de venta.',
            'items.*.product_id.required' => 'El identificador del producto es obligatorio.',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe en el catálogo.',
            'items.*.quantity.required' => 'La cantidad es obligatoria para cada producto.',
            'items.*.quantity.min' => 'La cantidad de cada producto debe ser de al menos 1 unidad.',
        ];
    }
}
