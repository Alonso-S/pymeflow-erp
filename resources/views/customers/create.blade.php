@extends('layouts.app', ['title' => 'Nuevo Cliente', 'header' => 'Contactos / Nuevo Cliente'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Cliente</h2>
            <p class="text-xs text-slate-500">Añada un nuevo cliente a la base de datos comercial</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-md transition-colors">
            &larr; Volver
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 sm:p-7">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre o Razón Social <span class="text-rose-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Ej. Comercializadora Andina S.A.S."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tax_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Doc. Identidad / DNI / RUT</label>
                    <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                           placeholder="Ej. 900123456-7"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono / Celular</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Ej. +57 300 1234567"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="contacto@empresa.com"
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>

            <div>
                <label for="address" class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección Física</label>
                <textarea id="address" name="address" rows="2"
                          placeholder="Calle, número, ciudad, detalles de entrega..."
                          class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none">{{ old('address') }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-2.5 pt-4 border-t border-slate-100">
                <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded-md border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Cancelar</a>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white text-xs font-semibold shadow-sm transition-colors">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection
