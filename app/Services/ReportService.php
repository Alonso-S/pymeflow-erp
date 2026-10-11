<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Obtiene los indicadores y métricas consolidadas para el Dashboard.
     */
    public function getDashboardMetrics(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Ventas del día
        $todaySales = Sale::completed()
            ->whereDate('created_at', $today)
            ->sum('total');

        $todaySalesCount = Sale::completed()
            ->whereDate('created_at', $today)
            ->count();

        // Ventas del mes
        $monthlySales = Sale::completed()
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total');

        $monthlySalesCount = Sale::completed()
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Productos activos y stock crítico
        $activeProductsCount = Product::active()->count();
        $lowStockProducts = Product::active()->lowStock()->with('category')->get();
        $lowStockCount = $lowStockProducts->count();

        // Últimas ventas registradas
        $latestSales = Sale::with(['user', 'customer'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Tendencia de ventas de los últimos 7 días
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayTotal = Sale::completed()
                ->whereDate('created_at', $date)
                ->sum('total');

            $salesTrend[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->translatedFormat('D d'),
                'total' => (float) $dayTotal,
            ];
        }

        // Productos más vendidos
        $topProducts = $this->getTopSellingProducts(5);

        return [
            'today_sales' => (float) $todaySales,
            'today_sales_count' => $todaySalesCount,
            'monthly_sales' => (float) $monthlySales,
            'monthly_sales_count' => $monthlySalesCount,
            'active_products_count' => $activeProductsCount,
            'low_stock_count' => $lowStockCount,
            'low_stock_products' => $lowStockProducts,
            'latest_sales' => $latestSales,
            'sales_trend' => $salesTrend,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Obtiene los productos con mayor volumen de unidades vendidas.
     */
    public function getTopSellingProducts(int $limit = 5)
    {
        return SaleItem::select(
            'product_id',
            'product_name',
            'product_sku',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->groupBy('product_id', 'product_name', 'product_sku')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Genera el contenido CSV del reporte de ventas filtrado por fechas.
     */
    public function generateSalesCsv(?string $startDate = null, ?string $endDate = null): string
    {
        $query = Sale::with(['user', 'customer', 'items'])->orderByDesc('created_at');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $sales = $query->get();

        $output = fopen('php://temp', 'r+');
        // BOM UTF-8 para apertura correcta en Excel
        fputs($output, "\xEF\xBB\xBF");

        // Encabezados
        fputcsv($output, ['N° Venta', 'Fecha', 'Vendedor', 'Cliente', 'Método Pago', 'Estado', 'Subtotal', 'Impuesto', 'Total', 'Cantidad Items']);

        foreach ($sales as $sale) {
            fputcsv($output, [
                $sale->sale_number,
                $sale->created_at->format('Y-m-d H:i:s'),
                $sale->user->name ?? 'N/A',
                $sale->customer_name,
                ucfirst($sale->payment_method),
                ucfirst($sale->status),
                number_format($sale->subtotal, 2, '.', ''),
                number_format($sale->tax, 2, '.', ''),
                number_format($sale->total, 2, '.', ''),
                $sale->items->sum('quantity'),
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Genera el contenido CSV del reporte de inventario actual.
     */
    public function generateInventoryCsv(): string
    {
        $products = Product::with('category')->orderBy('name')->get();

        $output = fopen('php://temp', 'r+');
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ['SKU', 'Producto', 'Categoría', 'Precio Costo', 'Precio Venta', 'Margen (%)', 'Stock Actual', 'Stock Mínimo', 'Estado Stock', 'Activo']);

        foreach ($products as $p) {
            $margin = $p->cost_price > 0 ? round((($p->sale_price - $p->cost_price) / $p->cost_price) * 100, 2) : 0;
            $stockStatus = $p->stock <= 0 ? 'Agotado' : ($p->stock <= $p->min_stock ? 'Stock Crítico' : 'Normal');

            fputcsv($output, [
                $p->sku,
                $p->name,
                $p->category->name ?? 'Sin categoría',
                number_format($p->cost_price, 2, '.', ''),
                number_format($p->sale_price, 2, '.', ''),
                $margin . '%',
                $p->stock,
                $p->min_stock,
                $stockStatus,
                $p->is_active ? 'Sí' : 'No',
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
