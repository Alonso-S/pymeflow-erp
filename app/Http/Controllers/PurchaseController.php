<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use DomainException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'user', 'items'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_document', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('company_name', 'LIKE', "%{$search}%")
                         ->orWhere('tax_id', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('company_name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('company_name')->get();
        $products = Product::active()->with('category')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        try {
            $purchase = $this->purchaseService->createPurchase(
                user: $request->user(),
                data: $request->validated()
            );

            $statusMsg = $purchase->isReceived()
                ? 'registrada y recepcionada en almacén'
                : 'registrada como orden pendiente de recepción';

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Orden de compra #{$purchase->purchase_number} {$statusMsg} por S/ " . number_format($purchase->total, 2) . ".");
        } catch (DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'receivedByUser', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function receive(Request $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->purchaseService->receivePurchase($purchase, $request->user());

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Mercadería de la orden #{$purchase->purchase_number} recepcionada exitosamente. El stock y los costos han sido actualizados en el Kardex.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al recepcionar la compra: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        try {
            $this->purchaseService->cancelPurchase($purchase, $request->user());

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "La orden de compra #{$purchase->purchase_number} fue cancelada.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cancelar la orden: ' . $e->getMessage());
        }
    }
}
