<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SupplierItemController extends Controller
{
    public function index()
    {
        try {
            $supplierItems = DB::table('supplier_items')
                ->select('supplier_items.*', 'suppliers.name as supplier_name', 'items.name as item_name')
                ->join('suppliers', 'supplier_items.supplier_id', '=', 'suppliers.id')
                ->join('items', 'supplier_items.item_id', '=', 'items.id')
                ->orderBy('supplier_items.id', 'desc')
                ->get();

            return response()->json($supplierItems);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to fetch supplier items', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'item_id' => 'required|exists:items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            $supplierItem = SupplierItem::create([
                'supplier_id' => $request->supplier_id,
                'item_id' => $request->item_id,
            ]);

            return response()->json(['message' => 'SupplierItem created successfully', 'data' => $supplierItem], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create supplier item', 'error' => $e->getMessage()], 500);
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

            $supplierItem->update($request->all());

            return response()->json(['message' => 'SupplierItem updated successfully', 'data' => $supplierItem], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to update supplier item', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $supplierItem = SupplierItem::find($id);

            if (is_null($supplierItem)) {
                return response()->json(['message' => 'SupplierItem not found'], 404);
            }

            $supplierItem->delete();

            return response()->json(['message' => 'SupplierItem deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to delete supplier item', 'error' => $e->getMessage()], 500);
        }
    }

    public function getItemsBySupplier($supplier_id)
    {
        try {
            $items = DB::table('supplier_items')
                ->join('items', 'supplier_items.item_id', '=', 'items.id')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->join('types', 'items.type_id', '=', 'types.id')
                ->where('supplier_items.supplier_id', $supplier_id)
                ->select('items.*', 'categories.name as category_name', 'types.name as type_name')
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['message' => 'No items found for this supplier'], 404);
            }

            return response()->json(['data' => $items], 200);
        } catch (Exception $e) {
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
