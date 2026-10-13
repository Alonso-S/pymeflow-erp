<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'supplier_document' => ['nullable', 'string', 'max:60'],
            'prices_include_tax' => ['nullable', 'boolean'],
            'receive_immediately' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Seleccione un proveedor válido.',
            'supplier_id.exists' => 'El proveedor seleccionado no existe.',
            'items.required' => 'La orden de compra debe incluir al menos un producto.',
            'items.min' => 'Agregue al menos un artículo a la orden de compra.',
            'items.*.product_id.required' => 'Seleccione un producto válido.',
            'items.*.quantity.min' => 'La cantidad a comprar debe ser de al menos 1 unidad.',
            'items.*.unit_cost.required' => 'Indique el costo unitario de compra.',
            'items.*.unit_cost.min' => 'El costo unitario debe ser mayor a cero.',
        ];
    }
}
