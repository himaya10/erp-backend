<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * List all sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'items.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        return response()->json($sales);
    }

    /**
     * Create a new sale with line items.
     *
     * BUSINESS LOGIC:
     * 1. Validate sufficient stock for all items
     * 2. Create sale + line items
     * 3. Decrease product quantity for each item
     * 4. Auto-create an income Transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Validate stock availability before starting the transaction
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for \"{$product->product_name}\". Available: {$product->quantity}, Requested: {$item['quantity']}",
                ], 422);
            }
        }

        $sale = DB::transaction(function () use ($request) {
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'total_amount' => $totalAmount,
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                // Decrease product stock
                Product::where('id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            // Auto-create income transaction
            Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'income',
                'amount' => $totalAmount,
                'reference_id' => 'SALE-' . $sale->id,
            ]);

            return $sale;
        });

        return response()->json($sale->load('items.product'), 201);
    }

    /**
     * Show a single sale.
     */
    public function show(Sale $sale)
    {
        return response()->json($sale->load(['user', 'items.product']));
    }
}
