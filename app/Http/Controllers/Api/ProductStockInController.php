<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageStock;
use Illuminate\Http\Request;
use App\Models\ProductStockIn;
use App\Models\FinishedProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductStockInController extends Controller
{
    public function index()
    {
        $productStockIns = ProductStockIn::with([
            'finishedProduct.stockOut.request.items.item.category',
            'finishedProduct.stockOut.request.items.item.type',
            'finishedProduct.stockOut.request.requestFor'
        ])->orderBy('id', 'desc')->get();

        return response()->json($productStockIns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'finished_product_id' => 'required|integer|exists:finished_products,id',
            'item_name' => 'required|string|max:255',
            'item_qty' => 'required|integer',
            'package_type' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'status' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        DB::beginTransaction();

        try {
            $productStockIn = ProductStockIn::create($request->all());

            // Update the corresponding FinishedProduct
            $finishedProduct = FinishedProduct::findOrFail($request->finished_product_id);
            $finishedProduct->item_qty -= $request->item_qty;

            if ($finishedProduct->item_qty < 0) {
                throw new \Exception('Not enough quantity in Finished Product');
            }

            $finishedProduct->save();

            // Update the corresponding PackageStock
            $packageDetails = explode(' - ', $request->package_type);
            if (count($packageDetails) < 3) {
                throw new \Exception('Invalid package type format');
            }

            $itemName = $packageDetails[0];
            $capacity = floatval($packageDetails[2]);

            $packageStock = PackageStock::where('item_name', $itemName)
                                        ->where('capacity', $capacity)
                                        ->first();

            if (!$packageStock) {
                throw new \Exception('Package stock not found');
            }

            $packageStock->quantity -= $request->quantity;

            if ($packageStock->quantity < 0) {
                throw new \Exception('Not enough quantity in Package Stock');
            }

            $packageStock->save();

            DB::commit();

            return response()->json(['message' => 'Product Stock In created successfully', 'data' => $productStockIn], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating Product Stock In', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $productStockIn = ProductStockIn::with([
            'finishedProduct.stockOut.request.item.item',
            'finishedProduct.stockOut.request.item.item.category',
            'finishedProduct.stockOut.request.item.item.type',
            'finishedProduct.stockOut.request.requestFor'
        ])->find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        return response()->json($productStockIn);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'finished_product_id' => 'sometimes|required|integer|exists:finished_products,id',
            'item_name' => 'sometimes|required|string|max:255',
            'item_qty' => 'sometimes|required|integer',
            'package_type' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer',
            'status' => 'sometimes|required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $productStockIn = ProductStockIn::find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        $productStockIn->update($request->all());

        return response()->json(['message' => 'Product Stock In updated successfully', 'data' => $productStockIn], 200);
    }

    public function destroy($id)
    {
        $productStockIn = ProductStockIn::find($id);

        if (is_null($productStockIn)) {
            return response()->json(['message' => 'Product Stock In not found'], 404);
        }

        $productStockIn->delete();

        return response()->json(['message' => 'Product Stock In deleted successfully'], 200);
    }
}
