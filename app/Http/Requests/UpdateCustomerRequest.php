<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:150'],
            'document_type' => ['nullable', 'string', 'in:DNI,RUC,CE,PASAPORTE'],
            'tax_id' => ['nullable', 'string', 'max:30', Rule::unique('customers', 'tax_id')->ignore($customerId)],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre o razón social del cliente es obligatorio.',
            'tax_id.unique' => 'Ya existe otro cliente registrado con este documento de identificación fiscal.',
            'email.email' => 'Ingrese una dirección de correo válida.',
        ];
    }
}
