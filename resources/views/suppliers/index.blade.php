@extends('layouts.app', ['title' => 'Gestión de Proveedores', 'header' => 'Contactos / Proveedores'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Directorio de Proveedores</h2>
            <p class="text-xs text-slate-500">Gestión de abastecimiento, distribuidores y contactos comerciales</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white font-medium text-xs shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Nuevo Proveedor
        </a>
    </div>

    <!-- Search bar -->
    <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center space-x-2.5">
            <label for="supplierSearch" class="sr-only">Buscar proveedores</label>
            <input type="text" id="supplierSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por Razón Social, Contacto, NIT/RUT o Correo..."
                   class="w-full max-w-lg px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors"
                   aria-label="Buscar proveedores por razón social, contacto o identificación">
            <button type="submit" class="py-1.5 px-3 rounded-md bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs transition-colors">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('suppliers.index') }}" class="py-1.5 px-2.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Empresa Proveedora</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3">NIT / Identificación</th>
                        <th class="px-4 py-3">Datos de Enlace</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ !$supplier->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $supplier->company_name }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $supplier->contact_name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-600">
                            @if($supplier->tax_id)
                                <span class="text-[10px] font-sans font-bold text-slate-500 mr-1">{{ $supplier->document_type ?? 'RUC' }}:</span>{{ $supplier->tax_id }}
                            @else
                                Sin documento
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-slate-800 block">{{ $supplier->phone ?? 'Sin teléfono' }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $supplier->email ?? 'Sin correo' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                                {{ $supplier->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center px-2 py-1 rounded text-slate-700 hover:bg-slate-100 border border-slate-200 text-xs font-medium transition-colors">
                                Editar
                            </a>
                            <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $supplier->is_active ? 'text-amber-800 hover:bg-amber-50 border border-amber-200' : 'text-emerald-800 hover:bg-emerald-50 border border-emerald-200' }} transition-colors">
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
