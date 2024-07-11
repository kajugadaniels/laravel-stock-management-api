<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('types', TypeController::class);
Route::apiResource('items', ItemController::class);
Route::get('items/supplier/{supplierId}', [ItemController::class, 'getItemsBySupplier']);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('products', StockInController::class);
Route::apiResource('stock-out', StockOutController::class);

//Registeration routes
Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});
