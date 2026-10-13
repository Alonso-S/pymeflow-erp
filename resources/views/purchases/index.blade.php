@extends('layouts.app', ['title' => 'Órdenes de Compra', 'header' => 'Abastecimiento / Órdenes de Compra'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Órdenes de Compra y Recepción</h2>
            <p class="text-xs text-slate-500">Gestión de compras a proveedores e ingreso controlado de mercadería al Kardex</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-900 hover:bg-brand-950 text-white font-medium text-xs tracking-wide shadow-sm transition">
            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Orden de Compra
        </a>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="purchaseSearch" class="sr-only">N° Compra, Doc. Proveedor o Razón Social</label>
                <input type="text" id="purchaseSearch" name="search" value="{{ request('search') }}" placeholder="N° Compra, Factura o Proveedor..."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
            </div>
            <div>
                <label for="purchaseSupplier" class="sr-only">Proveedor</label>
                <select id="purchaseSupplier" name="supplier_id" class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                    <option value="">Todos los Proveedores</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="purchaseStatus" class="sr-only">Estado</label>
                <select id="purchaseStatus" name="status" class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-brand-800 focus:border-brand-800 outline-none">
                    <option value="">Todos los Estados</option>
                    <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Pendiente de Recepción</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Recibida en Almacén</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'supplier_id', 'status']))
                <a href="{{ route('purchases.index') }}" class="p-2 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Limpiar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Doc. Referencia</th>
                        <th class="px-4 py-3 text-center">Estado Almacén</th>
                        <th class="px-4 py-3 text-right">IGV</th>
                        <th class="px-4 py-3 text-right">Total (PEN)</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $purchase)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-mono font-bold text-brand-900">
                            {{ $purchase->purchase_number }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $purchase->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-900 block">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                            @if($purchase->supplier && $purchase->supplier->tax_id)
                                <span class="text-[11px] font-mono text-slate-500">{{ $purchase->supplier->document_type ?? 'RUC' }}: {{ $purchase->supplier->tax_id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-600">
                            {{ $purchase->supplier_document ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($purchase->status === 'received')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Recibida en Almacén
                                </span>
                            @elseif($purchase->status === 'ordered')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    Pendiente de Recepción
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                    Anulada
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums text-slate-600">
                            S/ {{ number_format($purchase->tax, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold font-mono text-slate-900 text-sm tabular-nums">
                            S/ {{ number_format($purchase->total, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center px-2.5 py-1 rounded-md bg-brand-50 hover:bg-brand-100 text-brand-900 text-xs font-medium border border-brand-200 transition">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            No se encontraron órdenes de compra registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
