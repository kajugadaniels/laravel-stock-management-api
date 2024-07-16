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
        $stockIns = StockIn::orderBy('id', 'desc')
            ->with(['item.productItem'])
            ->get();

        return response()->json($stockIns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'registered_by' => 'required|string|max:255',
            'plaque' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'batch' => 'required|string|max:255',
            'status' => 'required|in:Complete,Pending',
            'loading_payment_status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::create($request->all());

        return response()->json(['message' => 'Stock In created successfully', 'data' => $stockIn], 201);
    }

    public function show($id)
    {
        $stockIn = StockIn::with('item')->find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In not found'], 404);
        }

        return response()->json($stockIn);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|exists:items,id',
            'quantity' => 'sometimes|required|integer',
            'registered_by' => 'sometimes|required|string|max:255',
            'plaque' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'batch' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:Complete,Pending',
            'loading_payment_status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In not found'], 404);
        }

        $stockIn->update($request->all());

        return response()->json(['message' => 'Stock In updated successfully', 'data' => $stockIn], 200);
    }

    public function destroy($id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In not found'], 404);
        }

        $stockIn->delete();

        return response()->json(['message' => 'Stock In deleted successfully'], 200);
    }
}
