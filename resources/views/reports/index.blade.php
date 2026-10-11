@extends('layouts.app', ['title' => 'Reportes y Analítica', 'header' => 'Reportes y Analítica Empresarial'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Reportes de Gestión</h2>
            <p class="text-xs text-slate-500">Métricas analíticas consolidadas y exportación de datos en formato CSV para Excel</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.export.sales', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="inline-flex items-center px-3.5 py-1.5 rounded-md bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs shadow-sm transition">
                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar Ventas (CSV)
            </a>
            <a href="{{ route('reports.export.inventory') }}"
               class="inline-flex items-center px-3.5 py-1.5 rounded-md bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs shadow-sm transition">
                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar Inventario (CSV)
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <label for="startDate" class="text-xs font-semibold text-slate-700 uppercase">Desde:</label>
                <input type="date" id="startDate" name="start_date" value="{{ $startDate }}"
                       class="px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                       aria-label="Fecha inicial del reporte">
            </div>
            <div class="flex items-center space-x-2">
                <label for="endDate" class="text-xs font-semibold text-slate-700 uppercase">Hasta:</label>
                <input type="date" id="endDate" name="end_date" value="{{ $endDate }}"
                       class="px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none"
                       aria-label="Fecha final del reporte">
            </div>
            <button type="submit" class="py-1.5 px-3.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                Aplicar Período
            </button>
        </form>
    </div>

    <!-- Period Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Facturación del Período</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tabular-nums font-mono">${{ number_format($salesTotal, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Total recaudado entre fechas</p>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Volumen de Transacciones</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1 tabular-nums font-mono">{{ $salesCount }} órdenes</h3>
            <p class="text-xs text-slate-500 mt-1">Ventas completadas con éxito</p>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ticket Promedio</p>
            <h3 class="text-2xl font-bold text-brand-900 mt-1 tabular-nums font-mono">${{ number_format($averageTicket, 2) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Monto promedio por transacción comercial</p>
        </div>
    </div>

    <!-- Tables: Top Products and Critical Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products in period -->
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900 mb-0.5">Ranking de Productos Más Vendidos</h3>
            <p class="text-xs text-slate-500 mb-4">Artículos con mayor demanda en el período</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-700 border-b border-slate-200 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5">Producto / SKU</th>
                            <th class="py-2.5 text-center">Unidades</th>
                            <th class="py-2.5 text-right">Facturado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topProducts as $top)
                        <tr>
                            <td class="py-2.5">
                                <span class="font-medium text-slate-900 block">{{ $top->product_name }}</span>
                                <span class="text-[11px] font-mono text-slate-500 block">{{ $top->product_sku }}</span>
                            </td>
                            <td class="py-2.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200 font-mono tabular-nums">
                                    {{ $top->total_quantity }} u.
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold font-mono tabular-nums text-slate-900">
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
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-0.5">
                <h3 class="text-sm font-semibold text-slate-900">Monitoreo de Existencias Críticas</h3>
                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $lowStockProducts->count() }} alertas
                </span>
            </div>
            <p class="text-xs text-slate-500 mb-4">Productos que requieren reabastecimiento</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-700 border-b border-slate-200 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-2.5">Producto</th>
                            <th class="py-2.5 text-center">Stock</th>
                            <th class="py-2.5 text-center">Mínimo</th>
                            <th class="py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lowStockProducts as $lp)
                        <tr>
                            <td class="py-2.5">
                                <span class="font-medium text-slate-900 block">{{ $lp->name }}</span>
                                <span class="text-[11px] font-mono text-slate-500 block">SKU: {{ $lp->sku }}</span>
                            </td>
                            <td class="py-2.5 text-center font-bold font-mono tabular-nums {{ $lp->stock <= 0 ? 'text-rose-700' : 'text-amber-800' }}">
                                {{ $lp->stock }}
                            </td>
                            <td class="py-2.5 text-center text-slate-700 font-mono tabular-nums">
                                {{ $lp->min_stock }}
                            </td>
                            <td class="py-2.5 text-right">
                                <a href="{{ route('inventory.create') }}" class="px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 font-medium text-[11px] border border-slate-200 transition">
                                    Reabastecer
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                <span class="inline-flex items-center gap-1.5 text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Todo el inventario está en niveles óptimos.
                                </span>
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
