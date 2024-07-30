<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\FinishedProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FinishedProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $finishedProducts = FinishedProduct::with([
            'stockOut.request.items.item',
            'stockOut.request.items.item.category',
            'stockOut.request.items.item.type',
            'stockOut.request.requestFor'
        ])->orderBy('id', 'desc')->get();

        return response()->json($finishedProducts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_out_id' => 'required|exists:stock_outs,id',
            'item_qty' => 'required|integer',
            'brand_qty' => 'required|integer',
            'dechet_qty' => 'required|integer',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $finishedProduct = FinishedProduct::create($request->all());

        $stockOut = $finishedProduct->stockOut;
        $stockOut->status = 'Finished';
        $stockOut->save();

        return response()->json(['message' => 'Finished Product created successfully', 'data' => $finishedProduct], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $finishedProduct = FinishedProduct::with([
            'stockOut.request.items.item',
            'stockOut.request.items.item.category',
            'stockOut.request.items.item.type',
            'stockOut.request.requestFor'
        ])->find($id);

        if (is_null($finishedProduct)) {
            return response()->json(['message' => 'Finished Product not found'], 404);
        }

        return response()->json($finishedProduct);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stock_out_id' => 'sometimes|required|exists:stock_outs,id',
            'item_qty' => 'sometimes|required|integer',
            'brand_qty' => 'sometimes|required|integer',
            'dechet_qty' => 'sometimes|required|integer',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $finishedProduct = FinishedProduct::find($id);

        if (is_null($finishedProduct)) {
            return response()->json(['message' => 'Finished Product not found'], 404);
        }

        $finishedProduct->update($request->all());

        return response()->json(['message' => 'Finished Product updated successfully', 'data' => $finishedProduct], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $finishedProduct = FinishedProduct::find($id);

        if (is_null($finishedProduct)) {
            return response()->json(['message' => 'Finished Product not found'], 404);
        }

        $finishedProduct->delete();

        return response()->json(['message' => 'Finished Product deleted successfully'], 200);
    }
}
