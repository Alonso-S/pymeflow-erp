<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function metrics(): JsonResponse
    {
        $metrics = $this->reportService->getDashboardMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ], 200);
    }
}
