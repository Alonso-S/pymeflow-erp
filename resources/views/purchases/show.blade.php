@extends('layouts.app', ['title' => "Orden de Compra {$purchase->purchase_number}", 'header' => 'Abastecimiento / Detalle de Compra'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('purchases.index') }}" class="text-xs font-medium text-slate-700 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-md shadow-sm transition">
            &larr; Volver a Órdenes de Compra
        </a>
        <div class="flex items-center gap-2">
            @if($purchase->status === 'ordered')
            <form action="{{ route('purchases.receive', $purchase) }}" method="POST" onsubmit="return confirm('¿Confirma la recepción física de la mercadería en almacén? Esto incrementará el stock de todos los productos en el Kardex.');">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 rounded-md bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-medium tracking-wide shadow-sm transition">
                    Confirmar Recepción de Mercadería (Ingresar Stock)
                </button>
            </form>
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('¿Desea anular esta orden de compra pendiente?');">
                @csrf
                <button type="submit" class="px-3.5 py-1.5 rounded-md bg-rose-700 hover:bg-rose-800 text-white text-xs font-medium tracking-wide shadow-sm transition">
                    Anular Orden
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-200 gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Orden de Compra Interna</span>
                <h2 class="text-xl font-bold font-mono text-slate-900 mt-0.5">{{ $purchase->purchase_number }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Registrada el {{ $purchase->created_at->format('d/m/Y H:i') }} por {{ $purchase->user->name ?? 'Sistema' }}</p>
            </div>
            <div class="text-right">
                @if($purchase->status === 'received')
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Recibida en Almacén ({{ optional($purchase->received_at)->format('d/m/Y H:i') }})
                    </span>
                @elseif($purchase->status === 'ordered')
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        Pendiente de Recepción (Stock no ingresado aún)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                        Orden Anulada
                    </span>
                @endif
            </div>
        </div>

        <div class="py-4 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Proveedor:</span>
                <span class="font-bold text-slate-900">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                @if($purchase->supplier && $purchase->supplier->tax_id)
                <span class="text-[11px] text-slate-500 block font-mono">{{ $purchase->supplier->document_type ?? 'RUC' }}: {{ $purchase->supplier->tax_id }}</span>
                @endif
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Comprobante Proveedor:</span>
                <span class="font-mono font-semibold text-slate-800">{{ $purchase->supplier_document ?? 'Sin documento adjunto' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Moneda:</span>
                <span class="font-semibold text-slate-800">{{ $purchase->currency ?? 'PEN' }} (Sol Peruano)</span>
            </div>
        </div>

        <div class="py-4 border-b border-slate-200">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-2">SKU</th>
                        <th class="py-2">Producto</th>
                        <th class="py-2 text-center">Cant.</th>
                        <th class="py-2 text-right">Costo Unit.</th>
                        <th class="py-2 text-right">Base Imp.</th>
                        <th class="py-2 text-right">IGV</th>
                        <th class="py-2 text-right">Total Línea</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                    <tr>
                        <td class="py-2.5 font-mono text-slate-600">{{ $item->product->sku ?? 'N/A' }}</td>
                        <td class="py-2.5 font-medium text-slate-900">{{ $item->product->name ?? 'Producto' }}</td>
                        <td class="py-2.5 text-center font-mono font-bold text-slate-800">{{ $item->quantity }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums text-slate-700">S/ {{ number_format($item->unit_cost, 2) }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums text-slate-600">S/ {{ number_format($item->tax_base, 2) }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums text-slate-600">S/ {{ number_format($item->tax_amount, 2) }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums font-bold text-slate-900">S/ {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div class="text-xs text-slate-600">
                @if($purchase->notes)
                    <span class="font-bold text-slate-800 uppercase text-[10px] block">Notas:</span>
                    {{ $purchase->notes }}
                @endif
            </div>
            <div class="w-full sm:w-64 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Valor de Compra (Sin IGV):</span>
                    <span class="font-mono font-semibold tabular-nums">S/ {{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>IGV (18%):</span>
                    <span class="font-mono font-semibold tabular-nums">S/ {{ number_format($purchase->tax, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 pt-1.5 border-t border-slate-200">
                    <span>TOTAL COMPRA:</span>
                    <span class="font-mono text-brand-950 text-base tabular-nums">S/ {{ number_format($purchase->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
