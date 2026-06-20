<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * List all purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'items.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }

    /**
     * Create a new purchase order with line items.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($request) {
            $totalCost = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $order = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'total_cost' => $totalCost,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $order;
        });

        return response()->json($order->load(['supplier', 'items.product']), 201);
    }

    /**
     * Show a single purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json($purchaseOrder->load(['supplier', 'user', 'items.product']));
    }

    /**
     * Delete a purchase order (only if pending).
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'message' => 'Cannot delete a received purchase order.',
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase order deleted successfully.']);
    }

    /**
     * Mark a purchase order as received.
     *
     * BUSINESS LOGIC:
     * 1. Update PO status to 'received'
     * 2. Increase stock for each line item's product
     * 3. Auto-create an expense Transaction
     */
    public function receive(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'message' => 'This purchase order has already been received.',
            ], 422);
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->update(['status' => 'received']);

            // Increase inventory for each item
            foreach ($purchaseOrder->items as $item) {
                $item->product->increment('quantity', $item->quantity);
            }

            // Auto-create expense transaction
            Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'expense',
                'amount' => $purchaseOrder->total_cost,
                'reference_id' => 'PO-' . $purchaseOrder->id,
            ]);
        });

        return response()->json($purchaseOrder->load(['supplier', 'items.product']));
    }

    /**
     * Cancel a purchase order (only if pending).
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchase orders can be cancelled.',
            ], 422);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json($purchaseOrder->load('supplier'));
    }
}
