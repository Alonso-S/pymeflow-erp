<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $minQuantity = ($type === 'adjustment') ? 0 : 1;

        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'string', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', "min:{$minQuantity}"],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Seleccione un producto para registrar el movimiento.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'type.required' => 'El tipo de movimiento es obligatorio.',
            'type.in' => 'El tipo debe ser entrada (in), salida (out) o ajuste (adjustment).',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad ingresada no es válida para este tipo de movimiento.',
            'reason.required' => 'Debe ingresar el motivo que justifica el movimiento.',
        ];
    }
}
