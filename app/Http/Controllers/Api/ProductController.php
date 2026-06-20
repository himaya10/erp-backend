<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List all products with optional search.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        return response()->json($products);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'type' => 'required|string|in:raw_material,finished_product',
            'price' => 'required|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
        ]);

        $product = Product::create($request->all());

        return response()->json($product, 201);
    }

    /**
     * Show a single product.
     */
    public function show(Product $product)
    {
        return response()->json($product->load(['productions', 'saleItems', 'purchaseOrderItems']));
    }

    /**
     * Update a product.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'type' => 'sometimes|string|in:raw_material,finished_product',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
        ]);

        $product->update($request->all());

        return response()->json($product);
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    /**
     * Get products below minimum stock level.
     */
    public function lowStock()
    {
        $products = Product::whereColumn('quantity', '<=', 'min_stock_level')->get();

        return response()->json($products);
    }

    /**
     * Manually adjust stock quantity.
     */
    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:set,add,subtract',
        ]);

        switch ($request->operation) {
            case 'set':
                $product->quantity = $request->quantity;
                break;
            case 'add':
                $product->quantity += $request->quantity;
                break;
            case 'subtract':
                $newQty = $product->quantity - $request->quantity;
                if ($newQty < 0) {
                    return response()->json(['message' => 'Cannot reduce stock below zero.'], 422);
                }
                $product->quantity = $newQty;
                break;
        }

        $product->save();

        return response()->json($product);
    }
}
