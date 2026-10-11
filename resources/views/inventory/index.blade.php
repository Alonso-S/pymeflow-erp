@extends('layouts.app', ['title' => 'Auditoría de Inventario', 'header' => 'Inventario / Movimientos'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Historial de Movimientos de Stock</h2>
            <p class="text-xs text-slate-500">Auditoría inmutable de entradas, salidas y ajustes de inventario</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-950 text-white font-medium text-xs tracking-wide shadow-sm transition">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Registrar Movimiento Manual
        </a>
        @endif
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="inventorySearch" class="sr-only">Buscar por motivo, SKU o producto</label>
                <input type="text" id="inventorySearch" name="search" value="{{ request('search') }}" placeholder="Buscar por motivo, SKU o producto..."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
            </div>
            <div>
                <label for="inventoryProduct" class="sr-only">Filtrar por producto</label>
                <select id="inventoryProduct" name="product_id" class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                    <option value="">Todos los Productos</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->sku }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="inventoryType" class="sr-only">Filtrar por tipo de movimiento</label>
                <select id="inventoryType" name="type" class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                    <option value="">Todos los Tipos</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Entradas (+ Compra / Ingreso)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Salidas (- Venta / Despacho)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajustes de Inventario</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'product_id', 'type']))
                <a href="{{ route('inventory.index') }}" class="p-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Limpiar filtros" aria-label="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Producto / SKU</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Stock Previo</th>
                        <th class="px-4 py-3 text-center">Variación</th>
                        <th class="px-4 py-3 text-center">Nuevo Stock</th>
                        <th class="px-4 py-3">Motivo / Justificación</th>
                        <th class="px-4 py-3">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-900 block">{{ $m->product->name ?? 'Producto eliminado' }}</span>
                            <span class="text-[11px] font-mono text-slate-500 block">SKU: {{ $m->product->sku ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($m->type === 'in')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                                Entrada
                            </span>
                            @elseif($m->type === 'out')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-rose-50 text-rose-800 border border-rose-200">
                                <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                                Salida
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Ajuste
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono tabular-nums font-medium text-slate-700">
                            {{ $m->previous_stock }}
                        </td>
                        <td class="px-4 py-3 text-center font-mono tabular-nums font-semibold">
                            @if($m->type === 'in')
                            <span class="text-emerald-700">+{{ $m->quantity }}</span>
                            @elseif($m->type === 'out')
                            <span class="text-rose-700">-{{ $m->quantity }}</span>
                            @else
                            <span class="text-slate-800">±{{ $m->quantity }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono tabular-nums font-bold text-slate-900">
                            {{ $m->new_stock }}
                        </td>
                        <td class="px-4 py-3 text-slate-700 max-w-xs">
                            {{ $m->reason }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 font-medium border border-slate-200">
                                {{ $m->user->name ?? 'Sistema' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            No se encontraron movimientos de inventario registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
