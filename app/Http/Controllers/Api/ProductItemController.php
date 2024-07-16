<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductItemController extends Controller
{
    public function index()
    {
        $productItems = ProductItem::orderBy('id', 'desc')->get();
        return response()->json($productItems);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productItem = ProductItem::create($request->all());

        return response()->json(['message' => 'Item created successfully', 'data' => $productItem], 201);
    }

    public function show(string $id)
    {
        $productItem = ProductItem::find($id);

        if (is_null($productItem)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($productItem);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productItem = ProductItem::find($id);

        if (is_null($productItem)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $productItem->update($request->all());

        return response()->json(['message' => 'Item updated successfully', 'data' => $productItem], 200);
    }

    public function destroy($id)
    {
        $productItem = ProductItem::find($id);

        if (is_null($productItem)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $productItem->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }
}
