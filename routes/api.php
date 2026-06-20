<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — ERP Lite & Inventory Management System
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
| Authentication is via Sanctum token-based auth.
| RBAC is enforced via the custom 'role' middleware.
|
*/

// ─── Public Routes ─────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected Routes ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ─── Dashboard (Admin + Managing Director) ─────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'summary'])
        ->middleware('role:admin,managing_director');

    // ─── Products ──────────────────────────────────────────────────────────
    // Read — most roles can view products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Write — only Admin + Inventory Officer
    Route::middleware('role:admin,inventory_officer')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
    });

    // ─── Productions ───────────────────────────────────────────────────────
    // Read — Admin, Managing Director, Production Manager
    Route::middleware('role:admin,managing_director,production_manager')->group(function () {
        Route::get('/productions', [ProductionController::class, 'index']);
        Route::get('/productions/{production}', [ProductionController::class, 'show']);
    });

    // Write — Admin + Production Manager
    Route::middleware('role:admin,production_manager')->group(function () {
        Route::post('/productions', [ProductionController::class, 'store']);
        Route::put('/productions/{production}', [ProductionController::class, 'update']);
        Route::delete('/productions/{production}', [ProductionController::class, 'destroy']);
        Route::patch('/productions/{production}/status', [ProductionController::class, 'updateStatus']);
    });

    // ─── Suppliers ─────────────────────────────────────────────────────────
    // Read — Admin, Managing Director, Purchasing Manager
    Route::middleware('role:admin,managing_director,purchasing_manager')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
    });

    // Write — Admin + Purchasing Manager
    Route::middleware('role:admin,purchasing_manager')->group(function () {
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);
    });

    // ─── Purchase Orders ───────────────────────────────────────────────────
    // Read — Admin, Managing Director, Purchasing Manager, Accountant
    Route::middleware('role:admin,managing_director,purchasing_manager,accountant')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
    });

    // Write — Admin + Purchasing Manager
    Route::middleware('role:admin,purchasing_manager')->group(function () {
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
        Route::patch('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        Route::patch('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
    });

    // ─── Sales ─────────────────────────────────────────────────────────────
    // Read — Admin, Managing Director, Sales Officer, Accountant
    Route::middleware('role:admin,managing_director,sales_officer,accountant')->group(function () {
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
    });

    // Write — Admin + Sales Officer
    Route::middleware('role:admin,sales_officer')->group(function () {
        Route::post('/sales', [SaleController::class, 'store']);
    });

    // ─── Transactions ──────────────────────────────────────────────────────
    // Read — Admin, Managing Director, Accountant
    Route::middleware('role:admin,managing_director,accountant')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/summary', [TransactionController::class, 'summary']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    });

    // Write — Admin + Accountant
    Route::middleware('role:admin,accountant')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'store']);
    });

    // ─── User Management (Admin only) ──────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/register', [AuthController::class, 'register']);
    });
});
