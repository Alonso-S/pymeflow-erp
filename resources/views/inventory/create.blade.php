@extends('layouts.app', ['title' => 'Registrar Movimiento de Inventario', 'header' => 'Inventario / Nuevo Movimiento'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Movimiento de Stock</h2>
            <p class="text-xs text-slate-500">Operación auditada para modificar existencias de almacén</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl">
            &larr; Volver
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('inventory.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Producto a Movilizar <span class="text-rose-500">*</span></label>
                <select id="product_id" name="product_id" required onchange="updateCurrentStock(this)"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">Seleccione un producto del catálogo</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} (SKU: {{ $p->sku }} - Stock actual: {{ $p->stock }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Stock Actual en Bodega:</span>
                <span id="currentStockDisplay" class="text-lg font-black font-mono text-slate-900">--</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="type" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Tipo de Movimiento <span class="text-rose-500">*</span></label>
                    <select id="type" name="type" required onchange="calculatePreview()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none font-semibold">
                        <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>🟢 Entrada (+ Compra / Ingreso)</option>
                        <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>🔴 Salida (- Merma / Despacho)</option>
                        <option value="adjustment" {{ old('type') === 'adjustment' ? 'selected' : '' }}>🔄 Ajuste Físico (Establecer Stock Exacto)</option>
                    </select>
                </div>

                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Cantidad de Unidades <span class="text-rose-500">*</span></label>
                    <input type="number" min="0" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required
                           oninput="calculatePreview()"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    <span id="quantityHelp" class="text-[11px] text-slate-400 mt-1 block">Unidades a sumar al stock actual.</span>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-center justify-between">
                <span class="text-xs font-semibold text-indigo-900">Nuevo Stock Estimado Resultante:</span>
                <span id="newStockPreview" class="text-lg font-black font-mono text-indigo-700">--</span>
            </div>

            <div>
                <label for="reason" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Motivo y Justificación <span class="text-rose-500">*</span></label>
                <input type="text" id="reason" name="reason" value="{{ old('reason') }}" required
                       placeholder="Ej. Compra factura #F-4421 a TechImports, corrección por conteo físico..."
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                <span class="text-[11px] text-slate-400 mt-1 block">Quedará registrado en la auditoría inmutable vinculado a su usuario.</span>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-md shadow-brand-500/20">Registrar Movimiento</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentStock = 0;

    function updateCurrentStock(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            currentStock = parseInt(option.getAttribute('data-stock')) || 0;
            document.getElementById('currentStockDisplay').textContent = `${currentStock} u.`;
        } else {
            currentStock = 0;
            document.getElementById('currentStockDisplay').textContent = '--';
        }
        calculatePreview();
    }

    function calculatePreview() {
        const type = document.getElementById('type').value;
        const qty = parseInt(document.getElementById('quantity').value) || 0;
        const preview = document.getElementById('newStockPreview');
        const help = document.getElementById('quantityHelp');

        let result = currentStock;

        if (type === 'in') {
            result = currentStock + qty;
            help.textContent = 'Unidades que se sumarán al inventario.';
        } else if (type === 'out') {
            result = currentStock - qty;
            help.textContent = 'Unidades que se restarán del inventario.';
        } else if (type === 'adjustment') {
            result = qty;
            help.textContent = 'El stock quedará fijado exactamente en esta cifra.';
        }

        if (result < 0) {
            preview.textContent = `${result} u. (⚠️ Stock Negativo No Permitido)`;
            preview.className = 'text-lg font-black font-mono text-rose-600';
        } else {
            preview.textContent = `${result} u.`;
            preview.className = 'text-lg font-black font-mono text-indigo-700';
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('product_id');
        if (select && select.value) {
            updateCurrentStock(select);
        }
    });
</script>
@endsection
