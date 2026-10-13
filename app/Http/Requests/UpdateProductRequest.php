<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('id');

        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'gte:cost_price'],
            'tax_type' => ['nullable', 'string', 'in:gravado,exonerado,inafecto'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.unique' => 'Ya existe otro producto registrado con este código SKU.',
            'name.required' => 'El nombre del producto es obligatorio.',
            'category_id.required' => 'Seleccione una categoría válida.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'cost_price.required' => 'El precio de costo es obligatorio.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.gte' => 'El precio de venta no puede ser inferior al precio de costo.',
            'min_stock.required' => 'El stock mínimo de alerta es obligatorio.',
        ];
    }
}
