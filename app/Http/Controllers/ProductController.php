<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->input('filter') === 'low_stock') {
            $query->lowStock();
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $initialStock = $data['stock'] ?? 0;
        $data['stock'] = 0; // Se inicializará a través de InventoryService

        $product = Product::create($data);

        if ($initialStock > 0) {
            $this->inventoryService->recordMovement(
                product: $product,
                user: $request->user(),
                type: 'in',
                quantity: $initialStock,
                reason: 'Inventario inicial de alta de producto',
                referenceType: 'ProductCreate',
                referenceId: $product->id
            );
        }

        return redirect()->route('products.index')
            ->with('success', "El producto '{$product->name}' (SKU: {$product->sku}) ha sido creado exitosamente.");
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        // El stock no se actualiza directamente aquí para proteger la auditoría inmutable
        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', "El producto '{$product->name}' fue actualizado con éxito.");
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $statusText = $product->is_active ? 'activado' : 'desactivado';

        return redirect()->back()
            ->with('success', "El producto '{$product->name}' ha sido {$statusText}.");
    }
}
