<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SupplierItemController extends Controller
{
    public function index()
    {
        try {
            $supplierItems = SupplierItem::where('delete_status', false)
                ->with(['supplier:id,name', 'item:id,name'])
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($supplierItem) {
                    return [
                        'id' => $supplierItem->id,
                        'supplier_id' => $supplierItem->supplier_id,
                        'item_id' => $supplierItem->item_id,
                        'supplier_name' => $supplierItem->supplier->name,
                        'item_name' => $supplierItem->item->name,
                        // Add any other fields you need
                    ];
                });

            return response()->json($supplierItems);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch supplier items', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $createdItems = [];
            $existingItems = [];

            foreach ($request->item_ids as $item_id) {
                $existingSupplierItem = SupplierItem::where('supplier_id', $request->supplier_id)
                    ->where('item_id', $item_id)
                    ->first();

                if (!$existingSupplierItem) {
                    $supplierItem = SupplierItem::create([
                        'supplier_id' => $request->supplier_id,
                        'item_id' => $item_id,
                    ]);
                    $createdItems[] = $supplierItem->load('item'); // Load the related item
                } else {
                    $existingItems[] = $existingSupplierItem->load('item'); // Load the related item
                }
            }

            DB::commit();

            return response()->json([
                'created_items' => $createdItems,
                'existing_items' => $existingItems
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create supplier items', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $supplierItem = SupplierItem::with(['supplier', 'item'])->find($id);

            if (is_null($supplierItem)) {
                return response()->json(['message' => 'SupplierItem not found'], 404);
            }

            return response()->json($supplierItem);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch supplier item', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'item_id' => 'sometimes|required|exists:items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            $supplierItem = SupplierItem::find($id);

            if (is_null($supplierItem)) {
                return response()->json(['message' => 'SupplierItem not found'], 404);
            }

            // Check if the combination of supplier_id and item_id already exists
            if ($request->has('supplier_id') && $request->has('item_id')) {
                $existingSupplierItem = SupplierItem::where('supplier_id', $request->supplier_id)
                    ->where('item_id', $request->item_id)
                    ->where('id', '!=', $id)
                    ->first();
                if ($existingSupplierItem) {
                    return response()->json(['message' => 'This supplier already supplies this item'], 400);
                }
            }

            $supplierItem->update($request->all());

            return response()->json(['message' => 'SupplierItem updated successfully', 'data' => $supplierItem], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update supplier item', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplierItem = SupplierItem::findOrFail($id);
            $supplierItem->update(['delete_status' => true]);

            return response()->json(['message' => 'SupplierItem soft deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to soft delete supplier item'], 500);
        }
    }

    public function getItemsBySupplier($supplier_id)
    {
        try {
            $items = SupplierItem::where('supplier_id', $supplier_id)
                ->where('delete_status', false)
                ->with(['item.category', 'item.type', 'supplier'])
                ->get()
                ->map(function ($supplierItem) {
                    $item = $supplierItem->item;
                    return [
                        'id' => $supplierItem->id, // Use the SupplierItem id
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'category_id' => $item->category_id,
                        'type_id' => $item->type_id,
                        'capacity' => $item->capacity,
                        'unit' => $item->unit,
                        'category_name' => $item->category->name,
                        'type_name' => $item->type->name,
                        'supplier_name' => $supplierItem->supplier->name,
                    ];
                });

            if ($items->isEmpty()) {
                return response()->json(['message' => 'No active items found for this supplier'], 404);
            }

            return response()->json(['data' => $items], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve items: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve items', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAvailableItems()
    {
        try {
            $items = DB::table('items')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->join('types', 'items.type_id', '=', 'types.id')
                ->where('categories.name', '!=', 'Finished')
                ->select('items.*', 'categories.name as category_name', 'types.name as type_name')
                ->get();

            return response()->json(['data' => $items], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch available items', 'error' => $e->getMessage()], 500);
        }
    }
}
