<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Items",
 *     description="API Endpoints for Items"
 * )
 */
class ItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/items",
     *     summary="Get all items",
     *     tags={"Items"},
     *     @OA\Response(
     *         response=200,
     *         description="List of items",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Item"))
     *     )
     * )
     */
    public function index()
    {
        $items = DB::table('items')
                    ->select('items.*', 'suppliers.name as supplier_name')
                    ->join('suppliers', 'items.supplier_id', '=', 'suppliers.id')
                    ->orderBy('items.id', 'desc')
                    ->get();

        return response()->json($items);
    }

    /**
     * @OA\Post(
     *     path="/api/items",
     *     summary="Create a new item",
     *     tags={"Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ItemRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Item"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/items/{id}",
     *     summary="Get an item by ID",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Item ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Item")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function show(string $id)
    {
        $item = Item::find($id);

        if (is_null($item)) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Put(
     *     path="/api/items/{id}",
     *     summary="Update an item",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Item ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ItemRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Item"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/items/{id}",
     *     summary="Delete an item",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Item ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
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
