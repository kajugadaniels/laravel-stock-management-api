<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockOuts = StockOut::with(['request.items.item', 'request.contactPerson', 'request.requestFor'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($stockOuts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Store method called', $request->all());

        $validator = Validator::make($request->all(), [
            'request_id' => 'required|integer|exists:requests,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::find($request->request_id);

        DB::beginTransaction();

        try {
            $totalRawMaterialQuantity = 0;

            // Create a new stock out record for each item
            foreach ($request->items as $item) {
                $stockIn = StockIn::find($item['item_id']);
                $category = $stockIn->item->category;

                if ($category->name === 'Raw Materials') {
                    $totalRawMaterialQuantity += $item['quantity'];
                } else {
                    StockOut::create([
                        'request_id' => $request->request_id,
                        'quantity' => $item['quantity'],
                        'date' => $request->date,
                        'status' => $request->status,
                    ]);

                    // Reduce the quantity in stock_in table
                    $stockIn->decrement('quantity', $item['quantity']);
                }
            }

            // Create a single stock out record for raw materials
            if ($totalRawMaterialQuantity > 0) {
                StockOut::create([
                    'request_id' => $request->request_id,
                    'quantity' => $totalRawMaterialQuantity,
                    'date' => $request->date,
                    'status' => $request->status,
                ]);
            }

            // Update request status to "Approved"
            $requestModel->update(['status' => 'Approved']);

            DB::commit();

            return response()->json(['message' => 'Stock out recorded successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record stock out', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to record stock out', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        Log::info('Show method called', ['id' => $id]);

        $stockOut = StockOut::with('request.items.item')->find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock out not found'], 404);
        }

        return response()->json($stockOut);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('UpdateStatus method called', ['request_data' => $request->all(), 'stock_out_id' => $id]);

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            Log::error('Stock out not found', ['stock_out_id' => $id]);
            return response()->json(['message' => 'Stock out not found'], 404);
        }

        DB::beginTransaction();

        try {
            $stockOut->status = $request->status;
            $stockOut->save();

            DB::commit();

            Log::info('Stock out status updated successfully', ['stock_out_id' => $id]);

            return response()->json(['message' => 'Stock out status updated successfully', 'data' => $stockOut], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock out status', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to update stock out status', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Log::info('Destroy method called', ['id' => $id]);

        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock out not found'], 404);
        }

        DB::beginTransaction();

        try {
            // Restore the quantity in stock_in table
            $stockIn = StockIn::find($stockOut->request->item_id);
            $stockIn->increment('quantity', $stockOut->quantity);

            // Delete the stock out record
            $stockOut->delete();

            DB::commit();

            return response()->json(['message' => 'Stock out deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete stock out', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to delete stock out', 'error' => $e->getMessage()], 500);
        }
    }
}
