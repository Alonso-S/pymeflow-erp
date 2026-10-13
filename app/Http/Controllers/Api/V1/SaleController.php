<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Sale::with(['user', 'customer', 'items'])->latest();

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $sales = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sales->items(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ]
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $sale = Sale::with(['user', 'customer', 'items.product'])->find($id);

        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sale,
        ], 200);
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $sale = $this->saleService->createSale(
                user: $request->user(),
                data: $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada con éxito.',
                'data' => $sale,
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Debe indicar el motivo de la anulación interna.',
        ]);

        try {
            $cancelledSale = $this->saleService->cancelSale(
                sale: $sale,
                user: $request->user(),
                reason: $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Venta anulada internamente y stock restituido con éxito.',
                'data' => $cancelledSale,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
