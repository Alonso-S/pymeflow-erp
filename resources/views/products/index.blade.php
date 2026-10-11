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
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md shadow-brand-500/20 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                + Nuevo Producto
            </a>
        </div>
        @endif
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU o Nombre..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>
            <div>
                <select name="category_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                    <option value="">Todas las Categorías</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="filter" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                    <option value="">Todos los Estados de Stock</option>
                    <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Solo Stock Crítico (Alerta)</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'category_id', 'filter']))
                <a href="{{ route('products.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold" title="Limpiar filtros">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">SKU / Código</th>
                        <th class="px-5 py-3.5">Producto</th>
                        <th class="px-5 py-3.5">Categoría</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-5 py-3.5 text-right">Costo</th>
                        @endif
                        <th class="px-5 py-3.5 text-right">Precio Venta</th>
                        <th class="px-5 py-3.5 text-center">Stock</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/70 transition {{ !$product->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                        <td class="px-5 py-4 font-mono font-bold text-slate-700">
                            {{ $product->sku }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-bold text-slate-900 block">{{ $product->name }}</span>
                            @if($product->description)
                            <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $product->description }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $product->category->name ?? 'Sin categoría' }}
                            </span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-5 py-4 text-right text-slate-500 font-mono">
                            ${{ number_format($product->cost_price, 2) }}
                        </td>
                        @endif
                        <td class="px-5 py-4 text-right font-bold font-mono text-slate-900">
                            ${{ number_format($product->sale_price, 2) }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($product->stock <= 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                Agotado (0)
                            </span>
                            @elseif($product->isLowStock())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800" title="Stock mínimo: {{ $product->min_stock }}">
                                ⚠️ {{ $product->stock }} (Mín: {{ $product->min_stock }})
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $product->stock }} disponibles
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-5 py-4 text-right space-x-2">
                            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 text-xs font-semibold transition">
                                Editar
                            </a>
                            <form action="{{ route('products.toggle-status', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium {{ $product->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}"
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
