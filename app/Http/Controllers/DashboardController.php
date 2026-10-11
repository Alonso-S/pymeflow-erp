<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        $metrics = $this->reportService->getDashboardMetrics();

        return view('dashboard.index', compact('metrics'));
    }
}
