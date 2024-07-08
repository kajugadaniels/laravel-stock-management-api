<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockOuts = StockOut::orderBy('id', 'desc')->get();
        return response()->json($stockOuts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_in_id' => 'required|exists:stock_ins,id',
            'quantity' => 'required|integer',
            'registered_by' => 'required|string|max:255',
            'request' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::create($request->all());

        return response()->json(['message' => 'StockOut created successfully', 'data' => $stockOut], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'StockOut not found'], 404);
        }

        return response()->json($stockOut);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stock_in_id' => 'sometimes|required|exists:stock_ins,id',
            'quantity' => 'sometimes|required|integer',
            'registered_by' => 'sometimes|required|string|max:255',
            'request' => 'sometimes|required|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'StockOut not found'], 404);
        }

        $stockOut->update($request->all());

        return response()->json(['message' => 'StockOut updated successfully', 'data' => $stockOut], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'StockOut not found'], 404);
        }

        $stockOut->delete();

        return response()->json(['message' => 'StockOut deleted successfully'], 200);
    }
}
