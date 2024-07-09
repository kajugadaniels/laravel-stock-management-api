<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        $items = DB::table('items')
                    ->select('items.*', 'suppliers.name as supplier_name')
                    ->join('suppliers', 'items.supplier_id', '=', 'suppliers.id')
                    ->orderBy('items.id', 'desc')
                    ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'capacity' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            $supplier = Supplier::findOrFail($request->supplier_id);

            $item = Item::create([
                'name' => $request->name,
                'category' => $request->category,
                'type' => $request->type,
                'capacity' => $request->capacity,
                'unit' => $request->unit,
                'supplier_id' => $request->supplier_id,
            ]);

            return response()->json(['message' => 'Item created successfully', 'data' => $item], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create item', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $item = Item::find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'capacity' => 'sometimes|required|numeric',
            'unit' => 'sometimes|required|string|max:50',
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $item = Item::find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->update($request->all());

        return response()->json(['message' => 'Item updated successfully', 'data' => $item], 200);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }
}
