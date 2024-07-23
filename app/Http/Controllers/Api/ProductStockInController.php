<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductStockIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductStockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productStockIns = ProductStockIn::with([
            'finishedProduct.stockOut.request.item.item',
            'finishedProduct.stockOut.request.item.item.category',
            'finishedProduct.stockOut.request.item.item.type',
            'finishedProduct.stockOut.request.requestFor'
        ])->orderBy('id', 'desc')->get();

        return response()->json($productStockIns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'finished_product_id' => 'required|integer|exists:finished_products,id',
            'item_name' => 'required|string|max:255',
            'item_qty' => 'required|integer',
            'package_type' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'status' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productStockIn = ProductStockIn::create($request->all());

        return response()->json(['message' => 'Product Stock In created successfully', 'data' => $productStockIn], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productStockIn = ProductStockIn::with([
            'finishedProduct.stockOut.request.item.item',
            'finishedProduct.stockOut.request.item.item.category',
            'finishedProduct.stockOut.request.item.item.type',
            'finishedProduct.stockOut.request.requestFor'
        ])->find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        return response()->json($productStockIn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'finished_product_id' => 'sometimes|required|integer|exists:finished_products,id',
            'item_name' => 'sometimes|required|string|max:255',
            'item_qty' => 'sometimes|required|integer',
            'package_type' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer',
            'status' => 'sometimes|required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productStockIn = ProductStockIn::find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        $productStockIn->update($request->all());

        return response()->json(['message' => 'Product Stock In updated successfully', 'data' => $productStockIn], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productStockIn = ProductStockIn::find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        $productStockIn->delete();

        return response()->json(['message' => 'Product Stock In deleted successfully'], 200);
    }
}
