@extends('layouts.app', ['title' => "Comprobante {$sale->sale_number}", 'header' => 'Ventas / Detalle de Operación'])

@section('content')
<style>
@media print {
    @page {
        size: 80mm auto;
        margin: 2mm;
    }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        overflow: visible !important;
    }
    aside, header, #sidebar, #openSidebarBtn, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
    }
    .ticket-card {
        max-width: 76mm !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 4mm !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        font-size: 11px !important;
        color: #000000 !important;
    }
    .ticket-card * {
        color: #000000 !important;
        border-color: #333333 !important;
    }
}
</style>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Action buttons (Hidden during printing) -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('sales.index') }}" class="text-xs font-medium text-slate-700 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-md shadow-sm transition">
            &larr; Volver al Historial
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('sales.create') }}" class="px-3.5 py-1.5 rounded-md bg-brand-900 hover:bg-brand-950 text-white text-xs font-medium tracking-wide shadow-sm transition">
                Nueva Venta
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium tracking-wide transition flex items-center shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Comprobante
            </button>
        </div>
    </div>

    <!-- Ticket Voucher Card -->
    <div class="ticket-card bg-white rounded-lg border border-slate-200 shadow-sm p-8">
        <!-- Ticket Header -->
        <div class="text-center pb-5 border-b border-dashed border-slate-300">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ config('erp.company_demo.trade_name', 'PymeFlow ERP') }}</h2>
            <p class="text-[11px] font-mono text-slate-600 mt-0.5">{{ config('erp.company_demo.legal_name', 'COMERCIALIZADORA PYMEFLOW PERÚ S.A.C.') }} • RUC: {{ config('erp.company_demo.ruc', '20608945123') }}</p>
            <p class="text-xs font-bold text-slate-700 mt-1.5 uppercase tracking-wide">COMPROBANTE INTERNO DE VENTA</p>
            <p class="text-[10px] text-slate-500">Documento de control interno — No válido como Comprobante de Pago Electrónico (CPE) SUNAT</p>
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-md font-mono font-bold text-sm text-slate-800 border border-slate-200">
                <span>{{ $sale->sale_number }}</span>
                @if($sale->status === 'cancelled')
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-sans font-bold uppercase bg-rose-100 text-rose-800 border border-rose-300">ANULADA</span>
                @endif
            </div>
        </div>

        @if($sale->status === 'cancelled')
        <div class="my-4 p-3 rounded-md bg-rose-50 border border-rose-200 text-xs text-rose-900">
            <span class="font-bold uppercase text-[10px] block text-rose-800">Operación Anulada — Stock Revertido en Kardex</span>
            <p class="mt-0.5"><span class="font-semibold">Motivo:</span> {{ $sale->cancellation_reason }}</p>
            <p class="text-[11px] text-rose-700 mt-0.5">
                Anulado el {{ optional($sale->cancelled_at)->format('d/m/Y H:i') }}
                @if($sale->cancelledByUser) por {{ $sale->cancelledByUser->name }} @endif
            </p>
        </div>
        @endif

        <!-- Metadata Grid -->
        <div class="py-5 border-b border-dashed border-slate-300 grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Fecha y Hora:</span>
                <span class="font-medium text-slate-800">{{ $sale->created_at->format('d/m/Y - H:i:s') }}</span>
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Atendido Por:</span>
                <span class="font-medium text-slate-800">{{ $sale->user->name ?? 'Usuario Sistema' }}</span>
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Cliente:</span>
                <span class="font-bold text-slate-900">{{ $sale->customer_name }}</span>
                @if($sale->customer && $sale->customer->tax_id)
                <span class="text-[11px] text-slate-500 block font-mono">{{ $sale->customer->document_type ?? 'DOC' }}: {{ $sale->customer->tax_id }}</span>
                @endif
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Medio de Pago / Moneda:</span>
                <span class="font-semibold text-slate-800 capitalize">{{ $sale->payment_method }} ({{ $sale->currency ?? 'PEN' }})</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-5 border-b border-dashed border-slate-300">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-600 font-bold border-b border-slate-200 pb-2">
                        <th class="py-1">Cant.</th>
                        <th class="py-1">Descripción / Afectación</th>
                        <th class="py-1 text-right">P. Unit</th>
                        <th class="py-1 text-right">Importe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="py-2.5 font-bold font-mono tabular-nums text-slate-800">{{ $item->quantity }}</td>
                        <td class="py-2.5">
                            <span class="font-semibold text-slate-900 block">{{ $item->product_name }}</span>
                            <span class="text-[11px] font-mono text-slate-500 block">
                                SKU: {{ $item->product_sku }} • {{ strtoupper($item->tax_type ?? 'gravado') }}
                            </span>
                        </td>
                        <td class="py-2.5 text-right font-mono tabular-nums text-slate-700">S/ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums font-bold text-slate-900">S/ {{ number_format($item->line_total > 0 ? $item->line_total : $item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary (Peruvian IGV Breakdown) -->
        <div class="pt-5 space-y-1.5 text-xs">
            <div class="flex justify-between text-slate-700">
                <span>Op. Gravada:</span>
                <span class="font-mono tabular-nums font-semibold">S/ {{ number_format($sale->taxed_subtotal > 0 || $sale->exempt_subtotal > 0 || $sale->unaffected_subtotal > 0 ? $sale->taxed_subtotal : $sale->subtotal, 2) }}</span>
            </div>
            @if(($sale->exempt_subtotal ?? 0) > 0)
            <div class="flex justify-between text-slate-700">
                <span>Op. Exonerada (0% IGV):</span>
                <span class="font-mono tabular-nums font-semibold">S/ {{ number_format($sale->exempt_subtotal, 2) }}</span>
            </div>
            @endif
            @if(($sale->unaffected_subtotal ?? 0) > 0)
            <div class="flex justify-between text-slate-700">
                <span>Op. Inafecta (0% IGV):</span>
                <span class="font-mono tabular-nums font-semibold">S/ {{ number_format($sale->unaffected_subtotal, 2) }}</span>
            </div>
            @endif
            @if(($sale->discount_total ?? 0) > 0)
            <div class="flex justify-between text-slate-700">
                <span>Descuento Total:</span>
                <span class="font-mono tabular-nums font-semibold text-rose-700">- S/ {{ number_format($sale->discount_total, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-slate-700">
                <span>IGV (18%):</span>
                <span class="font-mono tabular-nums font-semibold">S/ {{ number_format($sale->tax, 2) }}</span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                <span>IMPORTE TOTAL ({{ $sale->currency ?? 'PEN' }}):</span>
                <span class="font-mono tabular-nums text-emerald-800 text-lg">S/ {{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        @if($sale->notes)
        <div class="mt-6 p-3 rounded-md bg-slate-50 border border-slate-200 text-xs text-slate-700">
            <span class="font-bold text-slate-800 block text-[10px] uppercase">Observaciones:</span>
            {{ $sale->notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-8 pt-4 border-t border-slate-100 text-[11px] text-slate-500">
            <p class="font-medium text-slate-700">Comprobante de control interno — Gracias por su preferencia</p>
            <p class="font-mono text-[10px] mt-0.5">Emitido por PymeFlow ERP • Moneda: Sol Peruano (S/)</p>
        </div>
    </div>

    <!-- Admin Cancellation Panel (Hidden during printing) -->
    @if(auth()->user()->isAdmin() && $sale->status === 'completed')
    <div class="bg-white rounded-lg border border-rose-200 shadow-sm p-5 print:hidden">
        <h3 class="text-xs font-bold text-rose-900 uppercase tracking-wider">Anulación / Devolución de Venta</h3>
        <p class="text-xs text-slate-600 mt-1">
            Al anular esta operación, el sistema revertirá automáticamente el stock de todos los ítems en el Kardex y marcará el comprobante como anulado. Esta acción es irreversible.
        </p>
        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="mt-3 flex flex-col sm:flex-row gap-2.5" onsubmit="return confirm('¿Confirma la anulación definitiva de la venta {{ $sale->sale_number }} y el retorno del stock al inventario?');">
            @csrf
            <div class="flex-1">
                <label for="cancellation_reason" class="sr-only">Motivo de anulación</label>
                <input type="text" id="cancellation_reason" name="reason" required maxlength="500"
                       placeholder="Ingrese el motivo obligatorio de anulación o devolución..."
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-xs focus:ring-2 focus:ring-rose-600 focus:border-rose-600 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 rounded-md bg-rose-700 hover:bg-rose-800 text-white font-medium text-xs tracking-wide transition shadow-sm flex-shrink-0">
                Anular Venta y Revertir Stock
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
