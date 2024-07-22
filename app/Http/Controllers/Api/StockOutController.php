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
        $stockOuts = StockOut::with(['request.item', 'request.contactPerson', 'request.requestFor'])->orderBy('id', 'desc')->get();
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
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::find($request->request_id);
        $stockIn = StockIn::find($requestModel->item_id);

        if (!$stockIn || $stockIn->quantity < $request->quantity) {
            return response()->json(['message' => 'Insufficient quantity in stock'], 400);
        }

        // Start a transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Create a new stock out record
            $stockOut = StockOut::create([
                'request_id' => $request->request_id,
                'quantity' => $request->quantity,
                'date' => $request->date,
            ]);

            // Reduce the quantity in stock_in table
            $stockIn->decrement('quantity', $request->quantity);

            // Update request status to "Approved"
            $requestModel->update(['status' => 'Approved']);

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Stock out recorded successfully', 'data' => $stockOut], 201);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
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

        $stockOut = StockOut::with('request')->find($id);

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
        Log::info('Update method called', $request->all());

        $validator = Validator::make($request->all(), [
            'request_id' => 'sometimes|required|integer|exists:requests,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'date' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock out not found'], 404);
        }

        $requestModel = RequestModel::find($request->request_id);
        $stockIn = StockIn::find($requestModel->item_id);

        if ($request->has('quantity') && $request->quantity > ($stockIn->quantity + $stockOut->quantity)) {
            return response()->json(['message' => 'Insufficient quantity in stock'], 400);
        }

        // Start a transaction to ensure data consistency
        DB::beginTransaction();

        try {
            if ($request->has('quantity')) {
                $difference = $request->quantity - $stockOut->quantity;
                if ($difference > 0) {
                    $stockIn->decrement('quantity', $difference);
                } else {
                    $stockIn->increment('quantity', abs($difference));
                }
            }

            // Update the stock out record
            $stockOut->update($request->all());

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Stock out updated successfully', 'data' => $stockOut], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Failed to update stock out', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to update stock out', 'error' => $e->getMessage()], 500);
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

        // Start a transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Restore the quantity in stock_in table
            $stockIn = StockIn::find($stockOut->request->item_id);
            $stockIn->increment('quantity', $stockOut->quantity);

            // Delete the stock out record
            $stockOut->delete();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Stock out deleted successfully'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Failed to delete stock out', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to delete stock out', 'error' => $e->getMessage()], 500);
        }
    }
}
