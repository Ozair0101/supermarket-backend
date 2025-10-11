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
        
        $sales = $query->with(['customer', 'createdBy'])->get();
        
        $totalRevenue = $sales->sum('total');
        $totalTransactions = $sales->count();
        
        return response()->json([
            'sales' => $sales,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
            ]
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
        $lowStockThreshold = $request->query('low_stock_threshold', 10);
        
        // Instead of checking product quantity directly, we now check the sum of current_quantity
        // from purchase items for each product
        $products = Product::with(['purchaseItems'])->get();
        $lowStockProducts = $products->filter(function ($product) use ($lowStockThreshold) {
            $totalQuantity = $product->purchaseItems->sum('current_quantity');
            return $totalQuantity <= $lowStockThreshold;
        });
        
        $totalProducts = $products->count();
        $totalLowStockProducts = $lowStockProducts->count();
        
        return response()->json([
            'products' => $products,
            'low_stock_products' => $lowStockProducts,
            'summary' => [
                'total_products' => $totalProducts,
                'total_low_stock_products' => $totalLowStockProducts,
            ]
        ]);
    }
}
