<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // User management
        Route::apiResource('users', \App\Http\Controllers\UserController::class);

        // Role management
        Route::apiResource('roles', \App\Http\Controllers\RoleController::class);

        // Category management
        Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);

        // Product management
        Route::apiResource('products', \App\Http\Controllers\ProductController::class);

        // Supplier management
        Route::apiResource('suppliers', \App\Http\Controllers\SupplierController::class);

        // Customer management
        Route::apiResource('customers', \App\Http\Controllers\CustomerController::class);

        // Purchase management
        Route::apiResource('purchases', \App\Http\Controllers\PurchaseController::class);

        // Sale management
        Route::apiResource('sales', \App\Http\Controllers\SaleController::class);
        Route::get('sales/{id}/payments', '\App\Http\Controllers\SaleController@payments');

        // Payment management
        Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);
        Route::get('purchases/{id}/payments', '\App\Http\Controllers\PurchaseController@payments');

        // Inventory adjustment
        Route::apiResource('inventory-adjustments', \App\Http\Controllers\InventoryAdjustmentController::class);

        // Settings
        Route::apiResource('settings', \App\Http\Controllers\SettingController::class);

        // Reports
        Route::get('/reports/sales', [\App\Http\Controllers\ReportController::class, 'sales']);
        Route::get('/reports/purchases', [\App\Http\Controllers\ReportController::class, 'purchases']);
        Route::get('/reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory']);
        Route::get('/reports/dashboard', [\App\Http\Controllers\ReportController::class, 'dashboard']);
    });
});
