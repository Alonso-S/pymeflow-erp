<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use DomainException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index(Request $request): View
    {
        $query = Sale::with(['user', 'customer', 'items'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->paginate(10)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create(): View
    {
        $products = Product::active()
            ->where('stock', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        $customers = Customer::active()->orderBy('name')->get();

        return view('sales.create', compact('products', 'customers'));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $sale = $this->saleService->createSale(
                user: $request->user(),
                data: $request->validated()
            );

            return redirect()->route('sales.show', $sale)
                ->with('success', "¡Venta #{$sale->sale_number} completada con éxito por un total de S/ " . number_format($sale->total, 2) . "!");
        } catch (DomainException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error inesperado al procesar la venta: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'customer', 'items.product', 'cancelledByUser']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Debe especificar el motivo de la anulación interna.',
        ]);

        try {
            $this->saleService->cancelSale(
                sale: $sale,
                user: $request->user(),
                reason: $validated['reason']
            );

            return redirect()->route('sales.show', $sale)
                ->with('success', "La operación #{$sale->sale_number} ha sido anulada internamente y el stock fue restituido al almacén.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al anular la operación: ' . $e->getMessage());
        }
    }
}
