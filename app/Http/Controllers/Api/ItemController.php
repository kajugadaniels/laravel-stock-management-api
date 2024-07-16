<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Type;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        $items = DB::table('items')
            ->select('items.*', 'suppliers.name as supplier_name', 'categories.name as category_name', 'types.name as type_name')
            ->join('suppliers', 'items.supplier_id', '=', 'suppliers.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('types', 'items.type_id', '=', 'types.id')
            ->orderBy('items.id', 'desc')
            ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_item_id' => 'required|exists:product_items,id',
            'category_id' => 'required|exists:categories,id',
            'type_id' => 'nullable|exists:types,id',
            'capacity' => 'required|numeric',
            'unit' => 'nullable|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            $item = Item::create([
                'product_item_id' => $request->product_item_id,
                'category_id' => $request->category_id,
                'type_id' => $request->type_id,
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
        $item = Item::with(['supplier', 'category', 'type', 'productItem'])->find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_item_id' => 'sometimes|required|exists:product_items,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'type_id' => 'sometimes|nullable|exists:types,id',
            'capacity' => 'sometimes|required|numeric',
            'unit' => 'sometimes|nullable|string|max:50',
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

    public function getItemsBySupplier($supplierId)
    {
        $items = DB::table('items')
            ->select('items.*', 'suppliers.name as supplier_name', 'categories.name as category_name', 'types.name as type_name')
            ->join('suppliers', 'items.supplier_id', '=', 'suppliers.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('types', 'items.type_id', '=', 'types.id')
            ->where('items.supplier_id', $supplierId)
            ->orderBy('items.id', 'desc')
            ->get();

        return response()->json($items);
    }

    public function getTypesByCategory($categoryId)
    {
        $types = Type::where('category_id', $categoryId)
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }
}
