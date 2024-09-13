<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\SupplierItemController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FinishedProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PackageRequestController;
use App\Http\Controllers\Api\PackageStockController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\ProductStockInController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\ProductionStockInController;
use App\Http\Controllers\Api\ProductStockOutController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('types', TypeController::class);
Route::get('types/category/{categoryId}', [TypeController::class, 'getTypesByCategory']);
Route::get('raw-materials-and-packages', [TypeController::class, 'getRawMaterialsAndPackagesTypes']);
Route::apiResource('items', ItemController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('supplier-items', SupplierItemController::class);
Route::get('supplier-items/supplier/{supplier_id}', [SupplierItemController::class, 'getItemsBySupplier']);
Route::get('available-items', [SupplierItemController::class, 'getAvailableItems']);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('stock-ins', StockInController::class);
Route::apiResource('requests', RequestController::class);
Route::get('finished-items', [RequestController::class, 'getFinishedItems']);
Route::get('raw-material-items', [RequestController::class, 'getRawMaterialItems']);
Route::get('package-items', [RequestController::class, 'getPackageItems']);
Route::apiResource('stock-outs', StockOutController::class);
Route::post('/stock-outs/cancel/{id}', [StockOutController::class, 'cancel']);
Route::get('process/stock-outs', [ProcessController::class, 'getDetailedStockOuts']);
Route::get('package-stock-outs', [ProcessController::class, 'getDetailedPackageStockOuts']);
Route::get('unmerged-package-stock-outs', [ProcessController::class, 'getUnmergedPackageStockOuts']);
Route::apiResource('finished-products', FinishedProductController::class);
Route::apiResource('package-requests', PackageRequestController::class);
Route::apiResource('product-stock-ins', ProductStockInController::class);
Route::apiResource('production-stock-in', ProductionStockInController::class);
Route::apiResource('product-stock-out', ProductStockOutController::class);
Route::get('/inventory', [InventoryController::class, 'index']);
Route::get('/inventory/raw-materials', [InventoryController::class, 'rawMaterials']);
Route::get('/inventory/packages', [InventoryController::class, 'packages']);
Route::get('production-inventory', [InventoryController::class, 'productionInventory']);
Route::apiResource('package-stocks', PackageStockController::class);
Route::get('/dashboard', [DashboardController::class, 'index']);

// Registration routes
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::get('users', [RegisterController::class, 'getUsers']);
Route::put('/users/{id}', [RegisterController::class, 'updateUser']);
Route::delete('/users/{id}', [RegisterController::class, 'deleteUser']);
Route::post('change-password', [RegisterController::class, 'changePassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);
    Route::get('check-auth', [RegisterController::class, 'checkAuth']);
});
