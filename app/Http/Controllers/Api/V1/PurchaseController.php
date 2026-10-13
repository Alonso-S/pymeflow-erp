<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Purchase::with(['supplier', 'user', 'items'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $purchases = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $purchases->items(),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'per_page' => $purchases->perPage(),
                'total' => $purchases->total(),
            ],
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $purchase = Purchase::with(['supplier', 'user', 'receivedByUser', 'items.product'])->find($id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de compra no encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $purchase,
        ], 200);
    }

    public function store(StorePurchaseRequest $request): JsonResponse
    {
        try {
            $purchase = $this->purchaseService->createPurchase(
                user: $request->user(),
                data: $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Orden de compra registrada con éxito.',
                'data' => $purchase,
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receive(Request $request, int $id): JsonResponse
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Orden de compra no encontrada.',
            ], 404);
        }

        try {
            $received = $this->purchaseService->receivePurchase($purchase, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Mercadería recepcionada y stock actualizado.',
                'data' => $received,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
