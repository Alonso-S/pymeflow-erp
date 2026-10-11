@extends('layouts.app', ['title' => 'Gestión de Clientes', 'header' => 'Contactos / Clientes'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Directorio de Clientes</h2>
            <p class="text-xs text-slate-500">Gestión de cartera de clientes y compradores frecuentes</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            + Nuevo Cliente
        </a>
    </div>

    <!-- Search input -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center space-x-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por Nombre, Documento (DNI/RUT/RFC), Correo o Teléfono..."
                   class="w-full max-w-lg px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('customers.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                Limpiar
            </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Cliente / Razón Social</th>
                        <th class="px-5 py-3.5">Doc. Identidad</th>
                        <th class="px-5 py-3.5">Contacto</th>
                        <th class="px-5 py-3.5">Dirección</th>
                        <th class="px-5 py-3.5 text-center">Compras</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/70 transition {{ !$customer->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-5 py-4 font-bold text-slate-900">
                            {{ $customer->name }}
                        </td>
                        <td class="px-5 py-4 font-mono text-slate-700">
                            {{ $customer->tax_id ?? 'Sin documento' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-slate-800 block">{{ $customer->phone ?? 'Sin teléfono' }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $customer->email ?? 'Sin correo' }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-600 max-w-xs truncate">
                            {{ $customer->address ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                {{ $customer->sales_count }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                {{ $customer->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-2">
                            <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 text-xs font-semibold transition">
                                Editar
                            </a>
                            <form action="{{ route('customers.toggle-status', $customer) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium {{ $customer->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">
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
