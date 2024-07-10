<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::orderBy('id', 'desc')->get();
        return response()->json($stockIns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_received' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::create($request->all());

        return response()->json(['message' => 'Stock in entry created successfully', 'data' => $stockIn], 201);
    }

    public function show(string $id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        return response()->json($stockIn);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|exists:items,id',
            'quantity' => 'sometimes|required|numeric|min:1',
            'date_received' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        $stockIn->update($request->all());

        return response()->json(['message' => 'Stock In entry updated successfully', 'data' => $stockIn], 200);
    }

    public function destroy($id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        $stockIn->delete();

        return response()->json(['message' => 'Stock In entry deleted successfully'], 200);
    }
}
