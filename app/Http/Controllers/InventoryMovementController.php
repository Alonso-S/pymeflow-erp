<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryMovementRequest;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = InventoryMovement::with(['product.category', 'user'])->latest();

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('sku', 'LIKE', "%{$search}%");
                  });
            });
        }

        $movements = $query->paginate(15)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('inventory.index', compact('movements', 'products'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('inventory.create', compact('products'));
    }

    public function store(StoreInventoryMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        try {
            $movement = $this->inventoryService->recordMovement(
                product: $product,
                user: $request->user(),
                type: $data['type'],
                quantity: (int) $data['quantity'],
                reason: $data['reason'],
                referenceType: 'ManualAdjustment'
            );

            return redirect()->route('inventory.index')
                ->with('success', "Movimiento registrado exitosamente. Nuevo stock para '{$product->name}': {$product->stock} unidades.");
        } catch (DomainException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
