@extends('layouts.app', ['title' => 'Crear Producto', 'header' => 'Catálogo / Nuevo Producto'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Nuevo Producto</h2>
            <p class="text-xs text-slate-500">Ingrese la información del artículo y sus parámetros de inventario inicial</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl">
            &larr; Volver al Catálogo
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="sku" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Código SKU <span class="text-rose-500">*</span></label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required
                           placeholder="Ej. PRD-TEC-004"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none uppercase">
                    <span class="text-[11px] text-slate-400 mt-1 block">Identificador único del producto en inventario.</span>
                </div>

                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Categoría <span class="text-rose-500">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                        <option value="">Seleccione una categoría</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre del Producto <span class="text-rose-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       placeholder="Ej. Auriculares Bluetooth Cancelación de Ruido"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descripción Detallada</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Características técnicas, modelo, especificaciones..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Precios y Finanzas</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="cost_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Precio de Costo ($) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" value="{{ old('cost_price', '0.00') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
                               oninput="calculateMargin()">
                    </div>

                    <div>
                        <label for="sale_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Precio de Venta ($) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="{{ old('sale_price', '0.00') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none font-bold text-emerald-700"
                               oninput="calculateMargin()">
                        <div id="marginIndicator" class="text-[11px] font-semibold text-slate-500 mt-1">Margen estimado: 0%</div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Inventario Inicial</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="stock" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stock Inicial <span class="text-rose-500">*</span></label>
                        <input type="number" min="0" id="stock" name="stock" value="{{ old('stock', 0) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                        <span class="text-[11px] text-slate-400 mt-1 block">Creará automáticamente un movimiento auditado de alta.</span>
                    </div>

                    <div>
                        <label for="min_stock" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stock Mínimo de Alerta <span class="text-rose-500">*</span></label>
                        <input type="number" min="0" id="min_stock" name="min_stock" value="{{ old('min_stock', 5) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                        <span class="text-[11px] text-slate-400 mt-1 block">Umbral para disparar alertas de stock crítico.</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-md shadow-brand-500/20 transition">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateMargin() {
        const cost = parseFloat(document.getElementById('cost_price').value) || 0;
        const sale = parseFloat(document.getElementById('sale_price').value) || 0;
        const indicator = document.getElementById('marginIndicator');

        if (cost > 0 && sale >= cost) {
            const margin = (((sale - cost) / cost) * 100).toFixed(1);
            indicator.textContent = `Margen estimado: ${margin}% sobre el costo (Ganancia: $${(sale - cost).toFixed(2)})`;
            indicator.className = 'text-[11px] font-semibold text-emerald-600 mt-1';
        } else if (sale < cost) {
            indicator.textContent = '⚠️ Advertencia: El precio de venta es menor al costo';
            indicator.className = 'text-[11px] font-semibold text-rose-600 mt-1';
        } else {
            indicator.textContent = 'Margen estimado: 0%';
            indicator.className = 'text-[11px] font-semibold text-slate-500 mt-1';
        }
    }
    calculateMargin();
</script>
@endsection
