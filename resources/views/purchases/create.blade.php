@extends('layouts.app', ['title' => 'Nueva Orden de Compra', 'header' => 'Abastecimiento / Registrar Compra'])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registrar Orden de Compra</h2>
            <p class="text-xs text-slate-500">Registre una orden pendiente o confirme la recepción inmediata de mercadería en almacén</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 flex items-center">
            &larr; Volver a Compras
        </a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
        <form action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="supplier_id" class="block text-xs font-semibold text-slate-700 mb-1.5">Proveedor <span class="text-rose-600">*</span></label>
                    <select id="supplier_id" name="supplier_id" required
                            class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                        <option value="">Seleccione proveedor...</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} ({{ $supplier->document_type ?? 'RUC' }}: {{ $supplier->tax_id ?? 'S/D' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="supplier_document" class="block text-xs font-semibold text-slate-700 mb-1.5">Factura / Guía Proveedor</label>
                    <input type="text" id="supplier_document" name="supplier_document" value="{{ old('supplier_document') }}" placeholder="Ej. F001-0004582"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                </div>

                <div>
                    <label for="prices_include_tax" class="block text-xs font-semibold text-slate-700 mb-1.5">Modalidad IGV Costo</label>
                    <select id="prices_include_tax" name="prices_include_tax"
                            class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                        <option value="1" selected>Costo unitario incluye IGV (18%)</option>
                        <option value="0">Costo unitario más IGV (+18%)</option>
                    </select>
                </div>
            </div>

            <!-- Items Section -->
            <div class="border-t border-slate-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ítems de la Orden de Compra</h3>
                    <button type="button" onclick="addPurchaseRow()" class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-medium border border-slate-300 transition">
                        + Agregar Línea
                    </button>
                </div>

                <div id="purchaseRowsContainer" class="space-y-2.5">
                    <div class="purchase-row grid grid-cols-12 gap-2.5 items-end bg-slate-50 p-3 rounded-md border border-slate-200">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Producto</label>
                            <select name="items[0][product_id]" required onchange="syncDefaultCost(this, 0)"
                                    class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-brand-800 outline-none">
                                <option value="">Seleccione un producto...</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                                    {{ $product->sku }} — {{ $product->name }} (Stock actual: {{ $product->stock }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-5 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cantidad</label>
                            <input type="number" name="items[0][quantity]" value="1" min="1" required
                                   class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-brand-800 outline-none">
                        </div>
                        <div class="col-span-5 sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Costo Unit. (S/)</label>
                            <input type="number" step="0.01" min="0" name="items[0][unit_cost]" id="unit_cost_0" value="0.00" required
                                   class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-brand-800 outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex justify-end">
                            <button type="button" onclick="removePurchaseRow(this)" class="p-1.5 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Quitar fila">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-200">
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">Observaciones</label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Condiciones de entrega, guía de remisión, etc."
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="receive_immediately" value="1" class="rounded border-slate-300 text-brand-900 focus:ring-brand-800 w-4 h-4">
                        <span class="ml-2 text-xs font-semibold text-slate-800">Recibir mercadería inmediatamente en almacén (incrementa stock en Kardex)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2 rounded-md bg-brand-900 hover:bg-brand-950 text-white text-xs font-medium tracking-wide shadow-sm transition">
                    Registrar Orden de Compra
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let rowIdx = 1;
    const productOptionsHtml = `
        <option value="">Seleccione un producto...</option>
        @foreach($products as $product)
        <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">{{ $product->sku }} — {{ addslashes($product->name) }} (Stock actual: {{ $product->stock }})</option>
        @endforeach
    `;

    function syncDefaultCost(selectEl, idx) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const cost = opt ? opt.getAttribute('data-cost') : null;
        const costInput = document.getElementById(`unit_cost_${idx}`);
        if (cost && costInput) {
            costInput.value = parseFloat(cost).toFixed(2);
        }
    }

    function addPurchaseRow() {
        const container = document.getElementById('purchaseRowsContainer');
        const idx = rowIdx++;
        const row = document.createElement('div');
        row.className = 'purchase-row grid grid-cols-12 gap-2.5 items-end bg-slate-50 p-3 rounded-md border border-slate-200';
        row.innerHTML = `
            <div class="col-span-12 sm:col-span-6">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Producto</label>
                <select name="items[${idx}][product_id]" required onchange="syncDefaultCost(this, ${idx})"
                        class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-brand-800 outline-none">
                    ${productOptionsHtml}
                </select>
            </div>
            <div class="col-span-5 sm:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cantidad</label>
                <input type="number" name="items[${idx}][quantity]" value="1" min="1" required
                       class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-brand-800 outline-none">
            </div>
            <div class="col-span-5 sm:col-span-3">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Costo Unit. (S/)</label>
                <input type="number" step="0.01" min="0" name="items[${idx}][unit_cost]" id="unit_cost_${idx}" value="0.00" required
                       class="w-full px-3 py-1.5 rounded-md border border-slate-300 text-xs font-mono bg-white focus:ring-2 focus:ring-brand-800 outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1 flex justify-end">
                <button type="button" onclick="removePurchaseRow(this)" class="p-1.5 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Quitar fila">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function removePurchaseRow(btn) {
        const rows = document.querySelectorAll('.purchase-row');
        if (rows.length > 1) {
            btn.closest('.purchase-row').remove();
        }
    }
</script>
@endsection
