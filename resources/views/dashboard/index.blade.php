@extends('layouts.app', ['title' => 'Dashboard', 'header' => 'Panel de Control Principal'])

@section('content')
<div class="space-y-6">
    <!-- Welcome & Quick POS Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                    {{ auth()->user()->isAdmin() ? 'Rol: Administrador del Sistema' : 'Rol: Vendedor Comercial' }}
                </span>
                <span class="text-xs text-slate-400">• {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
            <h2 class="text-2xl font-black mt-1">¡Hola, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-slate-300 mt-0.5">Bienvenido al centro de operaciones de PymeFlow ERP. Aquí tienes el pulso de tu negocio en tiempo real.</p>
        </div>
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <a href="{{ route('sales.create') }}" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 transition transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Nueva Venta (POS)
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Ventas del Día -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ventas de Hoy</p>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($metrics['today_sales'], 2) }}</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $metrics['today_sales_count'] }} {{ Str::plural('transacción', $metrics['today_sales_count']) }} hoy</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Ventas del Mes -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ventas del Mes</p>
                    <h3 class="text-2xl font-black text-indigo-900 mt-1">${{ number_format($metrics['monthly_sales'], 2) }}</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $metrics['monthly_sales_count'] }} órdenes en {{ \Carbon\Carbon::now()->translatedFormat('F') }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Productos Activos -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Productos Activos</p>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $metrics['active_products_count'] }}</h3>
                    <a href="{{ route('products.index') }}" class="text-xs text-brand-600 hover:underline mt-1 inline-block font-semibold">Ver catálogo &rarr;</a>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Alertas de Stock Bajo -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stock Crítico</p>
                    <h3 class="text-2xl font-black {{ $metrics['low_stock_count'] > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                        {{ $metrics['low_stock_count'] }}
                    </h3>
                    <span class="text-xs text-slate-500 mt-1 block">Por debajo del stock mínimo</span>
                </div>
                <div class="w-12 h-12 rounded-xl {{ $metrics['low_stock_count'] > 0 ? 'bg-amber-50 border border-amber-200 text-amber-600' : 'bg-slate-50 border border-slate-200 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Trend Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Trend (Last 7 Days) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Evolución de Ventas (Últimos 7 días)</h3>
                    <p class="text-xs text-slate-500">Monto total facturado por día</p>
                </div>
            </div>

            <!-- CSS Bar Chart with exact DB metrics -->
            <div class="pt-6 pb-2">
                @php
                    $maxVal = max(array_column($metrics['sales_trend'], 'total')) ?: 1;
                @endphp
                <div class="grid grid-cols-7 gap-3 items-end h-48 border-b border-slate-200 pb-2">
                    @foreach($metrics['sales_trend'] as $day)
                    @php
                        $pct = max(8, round(($day['total'] / $maxVal) * 100));
                    @endphp
                    <div class="flex flex-col items-center group relative h-full justify-end">
                        <div class="text-[11px] font-bold text-slate-700 opacity-0 group-hover:opacity-100 transition mb-1">
                            ${{ number_format($day['total'], 0) }}
                        </div>
                        <div class="w-full bg-slate-100 rounded-t-lg overflow-hidden flex items-end h-36">
                            <div class="w-full bg-gradient-to-t from-brand-600 to-indigo-500 rounded-t-lg transition-all duration-500 group-hover:brightness-110" style="height: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 mt-2 block">{{ $day['day'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Top Productos Más Vendidos</h3>
                <p class="text-xs text-slate-500 mb-4">Por cantidad de unidades despachadas</p>

                <div class="space-y-3.5">
                    @forelse($metrics['top_products'] as $top)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/80 transition">
                        <div class="overflow-hidden pr-2">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $top->product_name }}</p>
                            <span class="text-[11px] font-mono text-slate-600 block">{{ $top->product_sku }}</span>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                                {{ $top->total_quantity }} u.
                            </span>
                            <span class="text-[11px] text-slate-500 block mt-0.5">${{ number_format($top->total_revenue, 2) }}</span>
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

    <!-- Bottom Tables: Latest Sales & Low Stock Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Latest Sales -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900">Últimas Ventas Registradas</h3>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Ver todas &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase tracking-wider">
                            <th class="py-2.5">N° Venta</th>
                            <th class="py-2.5">Cliente</th>
                            <th class="py-2.5 text-right">Total</th>
                            <th class="py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['latest_sales'] as $sale)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 font-semibold text-slate-800">
                                <a href="{{ route('sales.show', $sale) }}" class="text-brand-600 hover:underline font-mono">
                                    {{ $sale->sale_number }}
                                </a>
                                <span class="text-[10px] text-slate-500 block">{{ $sale->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 text-slate-600 truncate max-w-[130px]">{{ $sale->customer_name }}</td>
                            <td class="py-3 text-right font-bold text-slate-900">${{ number_format($sale->total, 2) }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-2 py-1 rounded bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-600 text-[11px] font-medium transition">
                                    Ticket
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Sin ventas recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-slate-900">Productos con Stock Crítico</h3>
                    @if($metrics['low_stock_count'] > 0)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                        {{ $metrics['low_stock_count'] }} alertas
                    </span>
                    @endif
                </div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('inventory.create') }}" class="text-xs font-bold text-amber-600 hover:underline">+ Reabastecer</a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase tracking-wider">
                            <th class="py-2.5">Producto</th>
                            <th class="py-2.5">SKU</th>
                            <th class="py-2.5 text-center">Stock</th>
                            <th class="py-2.5 text-center">Mínimo</th>
                            <th class="py-2.5 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['low_stock_products'] as $prod)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 font-semibold text-slate-800">{{ $prod->name }}</td>
                            <td class="py-3 font-mono text-slate-600">{{ $prod->sku }}</td>
                            <td class="py-3 text-center font-bold {{ $prod->stock <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ $prod->stock }}
                            </td>
                            <td class="py-3 text-center text-slate-600">{{ $prod->min_stock }}</td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $prod->stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $prod->stock <= 0 ? 'Agotado' : 'Bajo Stock' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-emerald-600">
                                ✓ Todo el catálogo se encuentra con niveles de stock óptimos.
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
