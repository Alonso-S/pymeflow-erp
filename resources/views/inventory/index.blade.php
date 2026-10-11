@extends('layouts.app', ['title' => 'Auditoría de Inventario', 'header' => 'Inventario / Movimientos'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Historial de Movimientos de Stock</h2>
            <p class="text-xs text-slate-500">Auditoría inmutable de entradas, salidas y ajustes de inventario</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            + Registrar Movimiento Manual
        </a>
        @endif
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por motivo, SKU o producto..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <select name="product_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">Todos los Productos</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->sku }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="type" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">Todos los Tipos</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Entradas (+ Compra / Ingreso)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Salidas (- Venta / Despacho)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajustes de Inventario</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'product_id', 'type']))
                <a href="{{ route('inventory.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold" title="Limpiar filtros">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5">Producto / SKU</th>
                        <th class="px-5 py-3.5 text-center">Tipo</th>
                        <th class="px-5 py-3.5 text-center">Stock Previo</th>
                        <th class="px-5 py-3.5 text-center">Variación</th>
                        <th class="px-5 py-3.5 text-center">Nuevo Stock</th>
                        <th class="px-5 py-3.5">Motivo / Justificación</th>
                        <th class="px-5 py-3.5">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-bold text-slate-900 block">{{ $m->product->name ?? 'Producto eliminado' }}</span>
                            <span class="text-[11px] font-mono text-slate-500 block">SKU: {{ $m->product->sku ?? 'N/A' }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($m->type === 'in')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                🟢 Entrada
                            </span>
                            @elseif($m->type === 'out')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                🔴 Salida
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                🔄 Ajuste
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center font-mono font-medium text-slate-600">
                            {{ $m->previous_stock }}
                        </td>
                        <td class="px-5 py-4 text-center font-mono font-bold">
                            @if($m->type === 'in')
                            <span class="text-emerald-600">+{{ $m->quantity }}</span>
                            @elseif($m->type === 'out')
                            <span class="text-rose-600">-{{ $m->quantity }}</span>
                            @else
                            <span class="text-indigo-600">±{{ $m->quantity }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center font-mono font-black text-slate-900">
                            {{ $m->new_stock }}
                        </td>
                        <td class="px-5 py-4 text-slate-700 max-w-xs">
                            {{ $m->reason }}
                        </td>
                        <td class="px-5 py-4 text-slate-600 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 font-medium">
                                {{ $m->user->name ?? 'Sistema' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-400">
                            No se encontraron movimientos de inventario registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
