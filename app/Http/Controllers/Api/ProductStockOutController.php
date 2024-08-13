<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ProductStockIn;
use App\Models\ProductStockOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductStockOutController extends Controller
{
    public function index()
    {
        $stockOuts = ProductStockOut::with(['productStockIn', 'employee'])->get();
        return response()->json($stockOuts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prod_stock_in_id' => 'required|integer|exists:product_stock_ins,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'location' => 'required|string',
            'plate' => 'required|string',
            'contact' => 'required|string',
            'loading_payment_status' => 'required|boolean',
            'comment' => 'nullable|string',
            'batch' => 'nullable|string',
            'client_name' => 'required|string',
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $productStockIn = ProductStockIn::findOrFail($request->prod_stock_in_id);

            if ($productStockIn->quantity < $request->quantity) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient stock quantity'], 400);
            }

            $productStockOut = new ProductStockOut($request->all());
            $productStockOut->save();

            $oldQuantity = $productStockIn->quantity;
            $productStockIn->decrement('quantity', $request->quantity);

            DB::commit();

            Log::info('Product Stock Out created and Stock In quantity updated', [
                'product_stock_out_id' => $productStockOut->id,
                'product_stock_in_id' => $productStockIn->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $productStockIn->quantity,
                'quantity_decremented' => $request->quantity
            ]);

            return response()->json(['message' => 'Product Stock Out created successfully', 'data' => $productStockOut], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Product Stock Out', [
                'error' => $e->getMessage(),
                'prod_stock_in_id' => $request->prod_stock_in_id,
                'quantity' => $request->quantity
            ]);
            return response()->json(['message' => 'Failed to create Product Stock Out', 'error' => $e->getMessage()], 500);
        }
    }
}
