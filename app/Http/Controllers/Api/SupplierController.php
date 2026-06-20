<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List all suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('created_at', 'desc')->get();

        return response()->json($suppliers);
    }

    /**
     * Create a new supplier.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json($supplier, 201);
    }

    /**
     * Show a single supplier with their purchase orders.
     */
    public function show(Supplier $supplier)
    {
        return response()->json($supplier->load('purchaseOrders'));
    }

    /**
     * Update a supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($request->all());

        return response()->json($supplier);
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully.']);
    }
}
