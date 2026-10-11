@extends('layouts.app', ['title' => 'Nuevo Cliente', 'header' => 'Contactos / Nuevo Cliente'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Cliente</h2>
            <p class="text-xs text-slate-500">Añada un nuevo cliente a la base de datos comercial</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl">
            &larr; Volver
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nombre o Razón Social <span class="text-rose-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Ej. Comercializadora Andina S.A.S."
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="tax_id" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Doc. Identidad / DNI / RUT</label>
                    <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                           placeholder="Ej. 900123456-7"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Teléfono / Celular</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Ej. +57 300 1234567"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="contacto@empresa.com"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <div>
                <label for="address" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Dirección Física</label>
                <textarea id="address" name="address" rows="2"
                          placeholder="Calle, número, ciudad, detalles de entrega..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">{{ old('address') }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-md shadow-brand-500/20">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection
