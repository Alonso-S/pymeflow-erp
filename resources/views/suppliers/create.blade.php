@extends('layouts.app', ['title' => 'Nuevo Proveedor', 'header' => 'Contactos / Nuevo Proveedor'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Proveedor</h2>
            <p class="text-xs text-slate-500">Añada un proveedor comercial para suministro de existencias</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-md transition-colors">
            &larr; Volver
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 sm:p-7">
        <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="company_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Razón Social o Empresa <span class="text-rose-500">*</span></label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required
                       placeholder="Ej. Distribuciones Tech S.A."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="contact_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Persona de Contacto</label>
                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}"
                           placeholder="Ej. Ing. Juan Pérez"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>

                <div>
                    <label for="document_type" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipo Documento</label>
                    <select id="document_type" name="document_type"
                            class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                        <option value="RUC" {{ old('document_type', 'RUC') === 'RUC' ? 'selected' : '' }}>RUC (11 dígitos)</option>
                        <option value="DNI" {{ old('document_type') === 'DNI' ? 'selected' : '' }}>DNI (8 dígitos)</option>
                    </select>
                </div>

                <div>
                    <label for="tax_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">N° RUC / Documento Fiscal</label>
                    <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                           placeholder="Ej. 20514879632"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm font-mono focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono Principal</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Ej. +57 1 8000123"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           placeholder="ventas@proveedor.com"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <div>
                <label for="address" class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección / Bodega</label>
                <textarea id="address" name="address" rows="2"
                          placeholder="Ubicación de despachos o instalaciones..."
                          class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">{{ old('address') }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-2.5 pt-4 border-t border-slate-100">
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 rounded-md border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Cancelar</a>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white text-xs font-semibold shadow-sm transition-colors">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>
@endsection
