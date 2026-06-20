<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Executive summary dashboard for Managing Director.
     */
    public function summary()
    {
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpenses = Transaction::where('type', 'expense')->sum('amount');

        $totalProducts = Product::count();
        $lowStockCount = Product::whereColumn('quantity', '<=', 'min_stock_level')->count();
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_stock_level')
            ->select('id', 'product_name', 'sku', 'quantity', 'min_stock_level')
            ->get();

        $productionStats = [
            'pending' => Production::where('status', 'pending')->count(),
            'in_progress' => Production::where('status', 'in_progress')->count(),
            'completed' => Production::where('status', 'completed')->count(),
        ];

        $totalSales = Sale::count();
        $totalSalesRevenue = Sale::sum('total_amount');

        $totalPurchaseOrders = PurchaseOrder::count();
        $pendingPOs = PurchaseOrder::where('status', 'pending')->count();

        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'financial' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'net_profit' => round($totalIncome - $totalExpenses, 2),
            ],
            'inventory' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'low_stock_products' => $lowStockProducts,
            ],
            'production' => $productionStats,
            'sales' => [
                'total_sales' => $totalSales,
                'total_revenue' => round($totalSalesRevenue, 2),
            ],
            'purchasing' => [
                'total_orders' => $totalPurchaseOrders,
                'pending_orders' => $pendingPOs,
            ],
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
