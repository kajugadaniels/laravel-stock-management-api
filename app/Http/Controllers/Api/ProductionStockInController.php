<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ProductionStockIn;
use App\Models\FinishedProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductionStockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productionStockIns = ProductionStockIn::orderBy('id', 'desc')->get();
        return response()->json($productionStockIns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'finished_product' => 'required|exists:finished_products,id',
            'package_type' => 'required|string',
            'quantity' => 'required|integer',
            'total_sacks' => 'required|integer',
            'remaining_kg' => 'required|integer',
            'remaining_sacks' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $finishedProduct = FinishedProduct::find($request->finished_product);

        $productionStockIn = ProductionStockIn::create([
            'finished_product' => $finishedProduct->id,
            'package_type' => $request->package_type,
            'quantity' => $request->quantity,
            'total_sacks' => $request->total_sacks,
            'remaining_kg' => $request->remaining_kg,
            'remaining_sacks' => $request->remaining_sacks,
        ]);

        return response()->json(['message' => 'Production Stock In created successfully', 'data' => $productionStockIn], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productionStockIn = ProductionStockIn::find($id);

        if (is_null($productionStockIn)) {
            return response()->json(['message' => 'Production Stock In not found'], 404);
        }

        return response()->json($productionStockIn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'finished_product' => 'sometimes|required|exists:finished_products,id',
            'package_type' => 'sometimes|required|string',
            'quantity' => 'sometimes|required|integer',
            'total_sacks' => 'sometimes|required|integer',
            'remaining_kg' => 'sometimes|required|integer',
            'remaining_sacks' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productionStockIn = ProductionStockIn::find($id);

        if (is_null($productionStockIn)) {
            return response()->json(['message' => 'Production Stock In not found'], 404);
        }

        $productionStockIn->update($request->all());

        return response()->json(['message' => 'Production Stock In updated successfully', 'data' => $productionStockIn], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $productionStockIn = ProductionStockIn::find($id);

        if (is_null($productionStockIn)) {
            return response()->json(['message' => 'Production Stock In not found'], 404);
        }

        $productionStockIn->delete();

        return response()->json(['message' => 'Production Stock In deleted successfully'], 200);
    }
}

