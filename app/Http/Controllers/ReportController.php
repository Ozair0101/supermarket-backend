<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Generate sales report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sales(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Sale::query();

        if ($startDate) {
            $query->where('sale_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('sale_date', '<=', $endDate);
        }

        $sales = $query->with(['customer', 'createdBy', 'items.product'])->get();

        $totalRevenue = $sales->sum('total');
        $totalTransactions = $sales->count();

        // Group sales by date for chart data
        $salesByDate = $sales->groupBy(function ($sale) {
            return date('Y-m-d', strtotime($sale->sale_date));
        })->map(function ($group) {
            return [
                'sales_count' => $group->count(),
                'revenue' => $group->sum('total')
            ];
        });

        return response()->json([
            'sales' => $sales,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
            ],
            'chart_data' => $salesByDate
        ]);
    }

    /**
     * Generate purchases report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchases(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Purchase::query();

        if ($startDate) {
            $query->where('purchase_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('purchase_date', '<=', $endDate);
        }

        $purchases = $query->with(['supplier', 'createdBy'])->get();

        $totalPurchases = $purchases->sum('total');
        $totalTransactions = $purchases->count();

        return response()->json([
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_transactions' => $totalTransactions,
            ]
        ]);
    }

    /**
     * Generate inventory report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventory(Request $request)
    {
        $lowStockThreshold = $request->query('low_stock_threshold', 5); // Changed default to 5 to match frontend

        // Get all products with their relationships
        $products = Product::with(['purchaseItems', 'saleItems'])->get();

        // Calculate inventory status
        $inStock = 0;
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($products as $product) {
            $totalQuantity = $product->total_quantity; // Uses the accessor from Product model

            if ($totalQuantity <= 0) {
                $outOfStock++;
            } elseif ($totalQuantity <= $lowStockThreshold) {
                $lowStock++;
            } else {
                $inStock++;
            }
        }

        $totalProducts = $products->count();
        $totalLowStockProducts = $lowStock;

        // Prepare chart data
        $inventoryChartData = [
            ['name' => 'In Stock', 'value' => $inStock],
            ['name' => 'Low Stock', 'value' => $lowStock],
            ['name' => 'Out of Stock', 'value' => $outOfStock],
        ];

        return response()->json([
            'products' => $products,
            'low_stock_products' => $products->filter(function ($product) use ($lowStockThreshold) {
                return $product->total_quantity <= $lowStockThreshold && $product->total_quantity > 0;
            }),
            'summary' => [
                'total_products' => $totalProducts,
                'total_low_stock_products' => $totalLowStockProducts,
            ],
            'chart_data' => $inventoryChartData
        ]);
    }
}
