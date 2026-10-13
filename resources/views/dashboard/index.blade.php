@extends('layouts.app', ['title' => 'Dashboard', 'header' => 'Panel de Control Principal'])

@section('content')
<div class="space-y-6">
    <!-- Welcome & Quick POS Bar -->
    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ auth()->user()->isAdmin() ? 'bg-brand-50 text-brand-800 border border-brand-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                    {{ auth()->user()->isAdmin() ? 'Administrador' : 'Vendedor' }}
                </span>
                <span class="text-xs text-slate-400">• {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mt-1">Panel de Control</h2>
            <p class="text-xs text-slate-500">Resumen operativo, volumen de ventas y alertas de inventario en tiempo real.</p>
        </div>
        <div class="flex items-center space-x-2.5 w-full md:w-auto">
            <a href="{{ route('sales.create') }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white font-medium text-xs shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Punto de Venta (POS)
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Ventas del Día -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ventas de Hoy</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 tabular-nums">S/ {{ number_format($metrics['today_sales'], 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">{{ $metrics['today_sales_count'] }} {{ Str::plural('transacción', $metrics['today_sales_count']) }}</p>
        </div>

        <!-- Card 2: Ventas del Mes -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ventas del Mes</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 tabular-nums">S/ {{ number_format($metrics['monthly_sales'], 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">{{ $metrics['monthly_sales_count'] }} órdenes en {{ \Carbon\Carbon::now()->translatedFormat('F') }}</p>
        </div>

        <!-- Card 3: Productos Activos -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Catálogo Activo</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 tabular-nums">{{ $metrics['active_products_count'] }}</h3>
            <a href="{{ route('products.index') }}" class="text-[11px] text-brand-700 hover:text-brand-900 mt-1 inline-block font-medium">Ver catálogo &rarr;</a>
        </div>

        <!-- Card 4: Alertas de Stock Bajo -->
        <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Stock Crítico</span>
                <svg class="w-4 h-4 {{ $metrics['low_stock_count'] > 0 ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold {{ $metrics['low_stock_count'] > 0 ? 'text-amber-800' : 'text-slate-900' }} tabular-nums">
                {{ $metrics['low_stock_count'] }}
            </h3>
            <span class="text-[11px] text-slate-500 mt-1 block font-medium">Artículos bajo stock mínimo</span>
        </div>
    </div>

    <!-- Charts and Trend Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Sales Trend (Last 7 Days) -->
        <div class="lg:col-span-2 bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Evolución de Ventas (Últimos 7 días)</h3>
                    <p class="text-xs text-slate-500">Monto total recaudado en Soles (PEN) por día</p>
                </div>
            </div>

            <!-- CSS Bar Chart with exact DB metrics -->
            <div class="pt-4 pb-1">
                @php
                    $maxVal = max(array_column($metrics['sales_trend'], 'total')) ?: 1;
                @endphp
                <div class="grid grid-cols-7 gap-3 items-end h-44 border-b border-slate-200 pb-2">
                    @foreach($metrics['sales_trend'] as $day)
                    @php
                        $pct = max(8, round(($day['total'] / $maxVal) * 100));
                    @endphp
                    <div class="flex flex-col items-center group relative h-full justify-end">
                        <div class="text-[10px] font-mono tabular-nums font-bold text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity mb-1">
                            S/ {{ number_format($day['total'], 0) }}
                        </div>
                        <div class="w-full bg-slate-100 rounded-t flex items-end h-32">
                            <div class="w-full bg-brand-800 hover:bg-brand-700 rounded-t transition-colors" style="height: {{ $pct }}%"></div>
                        </div>
                        <span class="text-[11px] font-medium text-slate-600 mt-2 block">{{ $day['day'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-0.5">Top Productos Más Vendidos</h3>
                <p class="text-xs text-slate-500 mb-3">Por volumen de unidades despachadas</p>

                <div class="space-y-2">
                    @forelse($metrics['top_products'] as $top)
                    <div class="flex items-center justify-between p-2 rounded-md bg-slate-50 border border-slate-200/60 hover:bg-slate-100/70 transition-colors">
                        <div class="overflow-hidden pr-2">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ $top->product_name }}</p>
                            <span class="text-[11px] font-mono text-slate-500 block">SKU: {{ $top->product_sku }}</span>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono font-semibold bg-slate-200 text-slate-800">
                                {{ $top->total_quantity }} u.
                            </span>
                            <span class="text-[11px] font-mono tabular-nums text-slate-600 block mt-0.5">S/ {{ number_format($top->total_revenue, 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        Aún no se han registrado ventas suficientes.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bottom Tables: Latest Sales & Low Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Latest Sales -->
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900">Últimas Ventas Registradas</h3>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-900">Ver todas &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider bg-slate-50">
                            <th class="px-3 py-2.5">N° Venta</th>
                            <th class="px-3 py-2.5">Cliente</th>
                            <th class="px-3 py-2.5 text-right">Total</th>
                            <th class="px-3 py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['latest_sales'] as $sale)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-3 py-2.5 font-medium text-slate-800">
                                <a href="{{ route('sales.show', $sale) }}" class="text-brand-700 hover:underline font-mono tabular-nums font-semibold">
                                    {{ $sale->sale_number }}
                                </a>
                                <span class="text-[10px] text-slate-500 block">{{ $sale->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-700 truncate max-w-[130px]">{{ $sale->customer_name }}</td>
                            <td class="px-3 py-2.5 text-right font-bold font-mono tabular-nums text-slate-900">S/ {{ number_format($sale->total, 2) }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-2 py-1 rounded text-slate-700 hover:bg-slate-100 border border-slate-200 text-[11px] font-medium transition-colors" aria-label="Ver ticket de la venta {{ $sale->sale_number }}">
                                    Ticket
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin ventas recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-bold text-slate-900">Productos con Stock Crítico</h3>
                    @if($metrics['low_stock_count'] > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-300">
                        {{ $metrics['low_stock_count'] }} alertas
                    </span>
                    @endif
                </div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('inventory.create') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-900">+ Reabastecer</a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider bg-slate-50">
                            <th class="px-3 py-2.5">Producto</th>
                            <th class="px-3 py-2.5">SKU</th>
                            <th class="px-3 py-2.5 text-center">Stock</th>
                            <th class="px-3 py-2.5 text-center">Mínimo</th>
                            <th class="px-3 py-2.5 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['low_stock_products'] as $prod)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-3 py-2.5 font-medium text-slate-800">{{ $prod->name }}</td>
                            <td class="px-3 py-2.5 font-mono text-slate-500">{{ $prod->sku }}</td>
                            <td class="px-3 py-2.5 text-center font-bold tabular-nums {{ $prod->stock <= 0 ? 'text-rose-700' : 'text-amber-700' }}">
                                {{ $prod->stock }}
                            </td>
                            <td class="px-3 py-2.5 text-center font-mono tabular-nums text-slate-600">{{ $prod->min_stock }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $prod->stock <= 0 ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-900 border border-amber-200' }}">
                                    {{ $prod->stock <= 0 ? 'Agotado' : 'Bajo Stock' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">
                                <span class="inline-flex items-center text-emerald-700 text-xs font-medium">
                                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Todos los productos cuentan con niveles de stock óptimos.
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
