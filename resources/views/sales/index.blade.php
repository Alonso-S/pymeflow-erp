@extends('layouts.app', ['title' => 'Historial de Ventas', 'header' => 'Ventas / Historial'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Historial de Ventas</h2>
            <p class="text-xs text-slate-500">Registro histórico de transacciones comerciales y comprobantes</p>
        </div>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-500/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            + Nueva Venta (POS)
        </a>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° Venta o Cliente..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none" title="Fecha inicial">
            </div>
            <div>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none" title="Fecha final">
            </div>
            <div>
                <select name="payment_method" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">Todos los Métodos</option>
                    <option value="efectivo" {{ request('payment_method') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ request('payment_method') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="transferencia" {{ request('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'start_date', 'end_date', 'payment_method']))
                <a href="{{ route('sales.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold" title="Limpiar filtros">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">N° Venta</th>
                        <th class="px-5 py-3.5">Fecha y Hora</th>
                        <th class="px-5 py-3.5">Cliente</th>
                        <th class="px-5 py-3.5">Atendido por</th>
                        <th class="px-5 py-3.5 text-center">Método</th>
                        <th class="px-5 py-3.5 text-center">Ítems</th>
                        <th class="px-5 py-3.5 text-right">Total</th>
                        <th class="px-5 py-3.5 text-right">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 font-mono font-bold text-brand-600">
                            {{ $sale->sale_number }}
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-900">
                            {{ $sale->customer_name }}
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            {{ $sale->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 capitalize">
                                {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center font-mono">
                            {{ $sale->items->sum('quantity') }} u.
                        </td>
                        <td class="px-5 py-4 text-right font-black font-mono text-emerald-600 text-sm">
                            ${{ number_format($sale->total, 2) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition">
                                Ver Ticket
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            No se encontraron ventas registradas con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
