<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TypeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('types', TypeController::class);
Route::apiResource('items', ItemController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('products', StockInController::class);
Route::apiResource('stock-out', StockOutController::class);
