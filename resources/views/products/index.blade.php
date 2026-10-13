@extends('layouts.app', ['title' => 'Catálogo de Productos', 'header' => 'Gestión de Productos'])

@section('content')
<div class="space-y-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Catálogo de Productos</h2>
            <p class="text-xs text-slate-500 mt-0.5">Control centralizado de inventario, precios y stock mínimo</p>
        </div>
        @if(auth()->user()->isAdmin())
        <div>
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-md bg-brand-900 hover:bg-brand-800 text-white font-medium text-xs shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Nuevo Producto
            </a>
        </div>
        @endif
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-lg p-3.5 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <div>
                <label for="productSearch" class="sr-only">Buscar por SKU o Nombre</label>
                <input type="text" id="productSearch" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU o Nombre..."
                       class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors"
                       aria-label="Buscar producto por código SKU o nombre">
            </div>
            <div>
                <label for="productCategoryFilter" class="sr-only">Filtrar por categoría</label>
                <select id="productCategoryFilter" name="category_id" class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors" aria-label="Filtrar por categoría">
                    <option value="">Todas las Categorías</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="productStockFilter" class="sr-only">Filtrar por estado de stock</label>
                <select id="productStockFilter" name="filter" class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors" aria-label="Filtrar por estado de stock">
                    <option value="">Todos los Estados de Stock</option>
                    <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Solo Stock Crítico (Alerta)</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-1.5 px-3 rounded-md bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs transition-colors">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'category_id', 'filter']))
                <a href="{{ route('products.index') }}" class="p-1.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold" title="Limpiar filtros" aria-label="Limpiar filtros">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">SKU / Código</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3 text-center">Afectación IGV</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-4 py-3 text-right">Costo (S/)</th>
                        @endif
                        <th class="px-4 py-3 text-right">Precio Venta (S/)</th>
                        <th class="px-4 py-3 text-center">Stock</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-4 py-3 text-right">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ !$product->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-4 py-3 font-mono font-medium text-slate-600">
                            {{ $product->sku }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-900 block">{{ $product->name }}</span>
                            @if($product->description)
                            <span class="text-[11px] text-slate-500 block truncate max-w-xs">{{ $product->description }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $product->category->name ?? 'Sin categoría' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $taxType = $product->tax_type ?? 'gravado'; @endphp
                            @if($taxType === 'gravado')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-brand-50 text-brand-900 border border-brand-200">
                                Gravado (18%)
                            </span>
                            @elseif($taxType === 'exonerado')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                Exonerado (0%)
                            </span>
                            @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                Inafecto (0%)
                            </span>
                            @endif
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-4 py-3 text-right text-slate-600 font-mono tabular-nums">
                            S/ {{ number_format($product->cost_price, 2) }}
                        </td>
                        @endif
                        <td class="px-4 py-3 text-right font-bold font-mono tabular-nums text-slate-900">
                            S/ {{ number_format($product->sale_price, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product->stock <= 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                Agotado (0)
                            </span>
                            @elseif($product->isLowStock())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-900 border border-amber-300" title="Stock mínimo: {{ $product->min_stock }}">
                                <svg class="w-3 h-3 mr-1 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                {{ $product->stock }} (Mín: {{ $product->min_stock }})
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                {{ $product->stock }} disponibles
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {{ $product->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-2 py-1 rounded text-slate-700 hover:bg-slate-100 border border-slate-200 text-xs font-medium transition-colors">
                                Editar
                            </a>
                            <form action="{{ route('products.toggle-status', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $product->is_active ? 'text-amber-800 hover:bg-amber-50 border border-amber-200' : 'text-emerald-800 hover:bg-emerald-50 border border-emerald-200' }} transition-colors"
                                        onclick="return confirm('¿Seguro que desea {{ $product->is_active ? 'desactivar' : 'activar' }} este producto?');">
                                    {{ $product->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? '8' : '6' }}" class="px-5 py-12 text-center">
                            <div class="max-w-xs mx-auto text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">No se encontraron productos</h3>
                                <p class="text-xs text-slate-500 mt-1">Pruebe ajustando los filtros de búsqueda o registre un nuevo artículo.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
