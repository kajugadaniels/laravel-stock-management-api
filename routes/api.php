<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\SupplierItemController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FinishedProductController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\ProductStockInController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\StockOutController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('types', TypeController::class);
Route::apiResource('items', ItemController::class);
Route::get('types/category/{categoryId}', [ItemController::class, 'getTypesByCategory']);
Route::apiResource('suppliers', SupplierController::class);
Route::get('supplier-items/supplier/{supplier_id}', [SupplierItemController::class, 'getItemsBySupplier']);
Route::apiResource('supplier-items', SupplierItemController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('stock-ins', StockInController::class);
Route::apiResource('requests', RequestController::class);
Route::apiResource('stock-outs', StockOutController::class);
Route::get('process/stock-outs', [ProcessController::class, 'getDetailedStockOuts']);
Route::apiResource('finished-products', FinishedProductController::class);
Route::apiResource('product-stock-ins', ProductStockInController::class);

// Registration routes
Route::controller(RegisterController::class)->group(function() {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->get('/check-auth', [RegisterController::class, 'checkAuth']);
