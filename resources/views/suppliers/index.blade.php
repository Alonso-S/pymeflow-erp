@extends('layouts.app', ['title' => 'Gestión de Proveedores', 'header' => 'Contactos / Proveedores'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Directorio de Proveedores</h2>
            <p class="text-xs text-slate-500">Gestión de abastecimiento, distribuidores y contactos comerciales</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            + Nuevo Proveedor
        </a>
    </div>

    <!-- Search bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center space-x-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por Razón Social, Contacto, NIT/RUT o Correo..."
                   class="w-full max-w-lg px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('suppliers.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Empresa Proveedora</th>
                        <th class="px-5 py-3.5">Contacto</th>
                        <th class="px-5 py-3.5">NIT / Identificación</th>
                        <th class="px-5 py-3.5">Datos de Enlace</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/70 transition {{ !$supplier->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ $supplier->company_name }}
                        </td>
                        <td class="px-5 py-4 text-slate-700">
                            {{ $supplier->contact_name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-600">
                            {{ $supplier->tax_id ?? 'Sin documento' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-slate-800 block">{{ $supplier->phone ?? 'Sin teléfono' }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $supplier->email ?? 'Sin correo' }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                {{ $supplier->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-2">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 text-xs font-semibold transition">
                                Editar
                            </a>
                            <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium {{ $supplier->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                    {{ $supplier->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                            No se encontraron proveedores registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
