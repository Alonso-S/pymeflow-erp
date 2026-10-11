@extends('layouts.app', ['title' => 'Historial de Ventas', 'header' => 'Ventas / Historial'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Historial de Ventas</h2>
            <p class="text-xs text-slate-500">Registro histórico de transacciones comerciales y comprobantes</p>
        </div>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-950 text-white font-medium text-xs tracking-wide shadow-sm transition">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Venta (POS)
        </a>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="saleSearch" class="sr-only">N° Venta o Cliente</label>
                <input type="text" id="saleSearch" name="search" value="{{ request('search') }}" placeholder="N° Venta o Cliente..."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                       aria-label="Buscar por número de venta o nombre de cliente">
            </div>
            <div>
                <label for="saleStartDate" class="sr-only">Fecha inicial</label>
                <input type="date" id="saleStartDate" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                       title="Fecha inicial" aria-label="Fecha inicial">
            </div>
            <div>
                <label for="saleEndDate" class="sr-only">Fecha final</label>
                <input type="date" id="saleEndDate" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                       title="Fecha final" aria-label="Fecha final">
            </div>
            <div>
                <label for="salePaymentMethod" class="sr-only">Método de pago</label>
                <select id="salePaymentMethod" name="payment_method" class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                        aria-label="Filtrar por método de pago">
                    <option value="">Todos los Métodos</option>
                    <option value="efectivo" {{ request('payment_method') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ request('payment_method') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="transferencia" {{ request('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'start_date', 'end_date', 'payment_method']))
                <a href="{{ route('sales.index') }}" class="p-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Limpiar filtros" aria-label="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">N° Venta</th>
                        <th class="px-4 py-3">Fecha y Hora</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Atendido por</th>
                        <th class="px-4 py-3 text-center">Método</th>
                        <th class="px-4 py-3 text-center">Ítems</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono font-bold text-brand-900">
                            {{ $sale->sale_number }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ $sale->customer_name }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $sale->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 capitalize border border-slate-200">
                                {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-mono tabular-nums text-slate-700 font-medium">
                            {{ $sale->items->sum('quantity') }} u.
                        </td>
                        <td class="px-4 py-3 text-right font-bold font-mono text-slate-900 text-sm">
                            ${{ number_format($sale->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-2.5 py-1 rounded-md bg-brand-50 hover:bg-brand-100 text-brand-900 text-xs font-medium border border-brand-200 transition">
                                Ver Ticket
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            No se encontraron ventas registradas con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
