<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * List all production runs.
     */
    public function index(Request $request)
    {
        $query = Production::with('product');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $productions = $query->orderBy('created_at', 'desc')->get();

        return response()->json($productions);
    }

    /**
     * Create a new production run.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'sometimes|string|in:pending,in_progress,completed',
            'production_date' => 'required|date',
        ]);

        $production = Production::create($request->all());

        return response()->json($production->load('product'), 201);
    }

    /**
     * Show a single production run.
     */
    public function show(Production $production)
    {
        return response()->json($production->load('product'));
    }

    /**
     * Update a production run.
     */
    public function update(Request $request, Production $production)
    {
        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'production_date' => 'sometimes|date',
        ]);

        $production->update($request->only(['product_id', 'quantity', 'production_date']));

        return response()->json($production->load('product'));
    }

    /**
     * Delete a production run.
     */
    public function destroy(Production $production)
    {
        // If it was completed, revert the stock increase
        if ($production->status === 'completed') {
            $production->product->decrement('quantity', $production->quantity);
        }

        $production->delete();

        return response()->json(['message' => 'Production run deleted successfully.']);
    }

    /**
     * Update production status with automated stock adjustment.
     *
     * BUSINESS LOGIC:
     * - When status changes TO 'completed': increase product quantity
     * - When status changes FROM 'completed': decrease product quantity (revert)
     */
    public function updateStatus(Request $request, Production $production)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $oldStatus = $production->status;
        $newStatus = $request->status;

        // No change
        if ($oldStatus === $newStatus) {
            return response()->json($production->load('product'));
        }

        $production->update(['status' => $newStatus]);

        // Completed → increase stock
        if ($oldStatus !== 'completed' && $newStatus === 'completed') {
            $production->product->increment('quantity', $production->quantity);
        }

        // Reverted from completed → decrease stock
        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $production->product->decrement('quantity', $production->quantity);
        }

        return response()->json($production->load('product'));
    }
}
