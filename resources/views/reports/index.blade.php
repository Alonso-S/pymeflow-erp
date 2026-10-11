@extends('layouts.app', ['title' => 'Reportes y Analítica', 'header' => 'Reportes y Analítica Empresarial'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Reportes de Gestión</h2>
            <p class="text-xs text-slate-500">Métricas analíticas consolidadas y exportación de datos en formato CSV para Excel</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.export.sales', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar Ventas (CSV)
            </a>
            <a href="{{ route('reports.export.inventory') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-md transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar Inventario (CSV)
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Desde:</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Hasta:</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <button type="submit" class="py-1.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                Aplicar Período
            </button>
        </form>
    </div>

    <!-- Period Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Facturación del Período</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($salesTotal, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Total recaudado entre las fechas seleccionadas</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Volumen de Transacciones</p>
            <h3 class="text-2xl font-black text-indigo-900 mt-1">{{ $salesCount }} órdenes</h3>
            <p class="text-xs text-slate-500 mt-1">Ventas completadas con éxito</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ticket Promedio</p>
            <h3 class="text-2xl font-black text-emerald-600 mt-1">${{ number_format($averageTicket, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Monto promedio por transacción comercial</p>
        </div>
    </div>

    <!-- Tables: Top Products and Critical Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products in period -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="text-base font-bold text-slate-900 mb-1">Ranking de Productos Más Vendidos</h3>
            <p class="text-xs text-slate-500 mb-4">Artículos con mayor demanda histórica</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase">
                            <th class="py-2.5">Producto / SKU</th>
                            <th class="py-2.5 text-center">Unidades</th>
                            <th class="py-2.5 text-right">Facturado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topProducts as $top)
                        <tr>
                            <td class="py-3">
                                <span class="font-bold text-slate-800 block">{{ $top->product_name }}</span>
                                <span class="text-[11px] font-mono text-slate-400 block">{{ $top->product_sku }}</span>
                            </td>
                            <td class="py-3 text-center font-bold font-mono text-indigo-700">
                                {{ $top->total_quantity }} u.
                            </td>
                            <td class="py-3 text-right font-black font-mono text-slate-900">
                                ${{ number_format($top->total_revenue, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-400">Sin datos de ventas registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alert in Inventory -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-base font-bold text-slate-900">Monitoreo de Existencias Críticas</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    {{ $lowStockProducts->count() }} alertas
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-4">Productos que requieren reabastecimiento urgente</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase">
                            <th class="py-2.5">Producto</th>
                            <th class="py-2.5 text-center">Stock</th>
                            <th class="py-2.5 text-center">Mínimo</th>
                            <th class="py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lowStockProducts as $lp)
                        <tr>
                            <td class="py-3">
                                <span class="font-bold text-slate-800 block">{{ $lp->name }}</span>
                                <span class="text-[11px] font-mono text-slate-400 block">SKU: {{ $lp->sku }}</span>
                            </td>
                            <td class="py-3 text-center font-bold font-mono {{ $lp->stock <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ $lp->stock }}
                            </td>
                            <td class="py-3 text-center text-slate-500 font-mono">
                                {{ $lp->min_stock }}
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('inventory.create') }}" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[11px] transition">
                                    Reabastecer
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-emerald-600 font-medium">
                                ✓ Todo el inventario está en niveles óptimos.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
