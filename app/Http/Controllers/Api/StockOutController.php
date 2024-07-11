<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StockOutController extends Controller
{
    public function index()
    {
        $stockOuts = StockOut::orderBy('id', 'desc')->with('stockIn')->get();
        return response()->json($stockOuts);
    }

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

        return response()->json(['message' => 'Stock Out created successfully', 'data' => $stockOut], 201);
    }

    public function show($id)
    {
        $stockOut = StockOut::with('stockIn')->find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock Out not found'], 404);
        }

        return response()->json($stockOut);
    }

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
            return response()->json(['message' => 'Stock Out not found'], 404);
        }

        $stockOut->update($request->all());

        return response()->json(['message' => 'Stock Out updated successfully', 'data' => $stockOut], 200);
    }

    public function destroy($id)
    {
        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock Out not found'], 404);
        }

        $stockOut->delete();

        return response()->json(['message' => 'Stock Out deleted successfully'], 200);
    }
}
