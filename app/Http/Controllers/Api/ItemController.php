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
                    ->select('items.*', 'categories.name as category_name', 'types.name as type_name')
                    ->join('categories', 'items.category_id', '=', 'categories.id')
                    ->join('types', 'items.type_id', '=', 'types.id')
                    ->orderBy('items.id', 'desc')
                    ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'type_id' => 'nullable|exists:types,id',
            'capacity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            $item = Item::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'type_id' => $request->type_id,
                'capacity' => $request->capacity,
                'unit' => $request->unit,
            ]);

            return response()->json(['message' => 'Item created successfully', 'data' => $item], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create item', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $item = Item::with(['category', 'type'])->find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'type_id' => 'sometimes|nullable|exists:types,id',
            'capacity' => 'sometimes|required|numeric',
            'unit' => 'sometimes|nullable|string|max:50',
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

    public function getTypesByCategory($categoryId)
    {
        $types = DB::table('types')
            ->select('id', 'name')
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }
}
