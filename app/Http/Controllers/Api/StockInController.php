<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockIns = StockIn::orderBy('id', 'desc')->get();
        return response()->json($stockIns);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'date' => 'required|date',
            'registered_by' => 'required|string|max:255',
            'plaque' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'batch' => 'required|string|max:255',
            'status' => 'required|in:Complete,Pending',
            'loading_payment_status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::create($request->all());

        return response()->json(['message' => 'StockIn created successfully', 'data' => $stockIn], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'StockIn not found'], 404);
        }

        return response()->json($stockIn);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|exists:items,id',
            'quantity' => 'sometimes|required|integer',
            'date' => 'sometimes|required|date',
            'registered_by' => 'sometimes|required|string|max:255',
            'plaque' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'batch' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:Complete,Pending',
            'loading_payment_status' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'StockIn not found'], 404);
        }

        $stockIn->update($request->all());

        return response()->json(['message' => 'StockIn updated successfully', 'data' => $stockIn], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'StockIn not found'], 404);
        }

        $stockIn->delete();

        return response()->json(['message' => 'StockIn deleted successfully'], 200);
    }
}
