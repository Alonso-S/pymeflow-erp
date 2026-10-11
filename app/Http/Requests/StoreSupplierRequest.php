<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:30', 'unique:suppliers,tax_id'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'La razón social o nombre de la empresa proveedora es obligatorio.',
            'tax_id.unique' => 'Ya existe un proveedor registrado con este documento de identificación fiscal.',
            'email.email' => 'Ingrese una dirección de correo válida.',
        ];
    }
}
