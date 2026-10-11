@extends('layouts.app', ['title' => 'Gestión de Clientes', 'header' => 'Contactos / Clientes'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Directorio de Clientes</h2>
            <p class="text-xs text-slate-500">Gestión de cartera de clientes y compradores frecuentes</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white font-medium text-xs shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Nuevo Cliente
        </a>
    </div>

    <!-- Search input -->
    <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center space-x-2.5">
            <label for="customerSearch" class="sr-only">Buscar clientes</label>
            <input type="text" id="customerSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por Nombre, Documento (DNI/RUT/RFC), Correo o Teléfono..."
                   class="w-full max-w-lg px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors"
                   aria-label="Buscar clientes por nombre, documento, correo o teléfono">
            <button type="submit" class="py-1.5 px-3 rounded-md bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs transition-colors">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('customers.index') }}" class="py-1.5 px-2.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition-colors">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Cliente / Razón Social</th>
                        <th class="px-4 py-3">Doc. Identidad</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3">Dirección</th>
                        <th class="px-4 py-3 text-center">Compras</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ !$customer->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $customer->name }}
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-600">
                            {{ $customer->tax_id ?? 'Sin documento' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-slate-800 block">{{ $customer->phone ?? 'Sin teléfono' }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $customer->email ?? 'Sin correo' }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                            {{ $customer->address ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono tabular-nums font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $customer->sales_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {{ $customer->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                                {{ $customer->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-2 py-1 rounded text-slate-700 hover:bg-slate-100 border border-slate-200 text-xs font-medium transition-colors">
                                Editar
                            </a>
                            <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $customer->is_active ? 'text-amber-800 hover:bg-amber-50 border border-amber-200' : 'text-emerald-800 hover:bg-emerald-50 border border-emerald-200' }} transition-colors">
                                    {{ $customer->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            No se encontraron clientes registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
