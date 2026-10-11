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
        <div class="text-center pb-6 border-b border-dashed border-slate-300">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">PymeFlow ERP</h2>
            <p class="text-xs text-slate-500 mt-0.5">COMPROBANTE INTERNO DE VENTA</p>
            <div class="mt-3 inline-block px-3 py-1 bg-slate-100 rounded-lg font-mono font-bold text-sm text-slate-800">
                {{ $sale->sale_number }}
            </div>
        </div>

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
                <span class="text-[11px] text-slate-500 block font-mono">Doc: {{ $sale->customer->tax_id }}</span>
                @endif
            </div>
            <div>
                <span class="text-slate-500 block font-bold uppercase text-[10px]">Forma de Pago:</span>
                <span class="font-semibold text-slate-800 capitalize">{{ $sale->payment_method }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-5 border-b border-dashed border-slate-300">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-600 font-bold border-b border-slate-200 pb-2">
                        <th class="py-1">Cant.</th>
                        <th class="py-1">Descripción</th>
                        <th class="py-1 text-right">P. Unit</th>
                        <th class="py-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="py-2.5 font-bold font-mono tabular-nums text-slate-800">{{ $item->quantity }}</td>
                        <td class="py-2.5">
                            <span class="font-semibold text-slate-900 block">{{ $item->product_name }}</span>
                            <span class="text-[11px] font-mono text-slate-500 block">SKU: {{ $item->product_sku }}</span>
                        </td>
                        <td class="py-2.5 text-right font-mono tabular-nums text-slate-700">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 text-right font-mono tabular-nums font-bold text-slate-900">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary -->
        <div class="pt-5 space-y-2 text-xs">
            <div class="flex justify-between text-slate-700">
                <span>Subtotal:</span>
                <span class="font-mono tabular-nums font-semibold">${{ number_format($sale->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-700">
                <span>Impuesto:</span>
                <span class="font-mono tabular-nums font-semibold">${{ number_format($sale->tax, 2) }}</span>
            </div>
            <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                <span>TOTAL OPERACIÓN:</span>
                <span class="font-mono tabular-nums text-emerald-700 text-lg">${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        @if($sale->notes)
        <div class="mt-6 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700">
            <span class="font-bold text-slate-800 block text-[10px] uppercase">Nota:</span>
            {{ $sale->notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-8 pt-4 border-t border-slate-100 text-[11px] text-slate-500">
            <p class="font-medium text-slate-700">¡Gracias por su compra!</p>
            <p class="font-mono text-[10px] mt-0.5">Generado automáticamente por PymeFlow ERP Cloud</p>
        </div>
    </div>
</div>
@endsection
