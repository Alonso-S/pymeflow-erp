<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $salesQuery = Sale::completed()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $salesTotal = (float) $salesQuery->sum('total');
        $salesTaxTotal = (float) (clone $salesQuery)->sum('tax');
        $salesCount = $salesQuery->count();
        $averageTicket = $salesCount > 0 ? round($salesTotal / $salesCount, 2) : 0;

        $topProducts = $this->reportService->getTopSellingProducts(8, $startDate, $endDate);
        $lowStockProducts = Product::active()->lowStock()->with('category')->get();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'salesTotal',
            'salesTaxTotal',
            'salesCount',
            'averageTicket',
            'topProducts',
            'lowStockProducts'
        ));
    }

    public function exportSalesCsv(Request $request): Response
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $csv = $this->reportService->generateSalesCsv($startDate, $endDate);
        $filename = 'reporte_ventas_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportInventoryCsv(): Response
    {
        $csv = $this->reportService->generateInventoryCsv();
        $filename = 'reporte_inventario_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
