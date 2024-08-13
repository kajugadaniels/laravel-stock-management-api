<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\PackageStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Validator;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockOut::with([
            'request.items' => function ($query) {
                $query->with([
                    'item' => function ($itemQuery) {
                        $itemQuery->select('id', 'name', 'capacity', 'unit', 'category_id', 'type_id');
                    },
                    'item.category',
                    'item.type',
                    'supplier'
                ]);
            },
            'request.contactPerson',
            'request.requestFor'
        ])
        ->orderBy('id', 'desc');

        // Handle date filters
        if ($request->filled('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        // Handle status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Handle requester filter
        if ($request->filled('requester')) {
            $query->whereHas('request', function ($q) use ($request) {
                $q->where('requester_name', 'like', '%' . $request->requester . '%');
            });
        }

        $stockOuts = $query->get();

        // Group stock-outs by request_id
        $groupedStockOuts = $stockOuts->groupBy('request_id')->map(function ($group) {
            $firstItem = $group->first();
            return [
                'id' => $firstItem->id,
                'request_id' => $firstItem->request_id,
                'request' => $firstItem->request,
                'status' => $firstItem->status,
                'date' => $firstItem->date,
                'total_quantity' => $group->sum('quantity'),
                'items' => $group->pluck('request.items')->flatten()->unique('id')->values(),
            ];
        })->values();

        return response()->json($groupedStockOuts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('StockOut store method called', ['request' => $request->all()]);

        try {
            $validator = Validator::make($request->all(), [
                'request_id' => 'required|integer|exists:requests,id',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|integer|exists:stock_ins,id',
                'items.*.quantity' => 'required|integer|min:1',
                'date' => 'required|date',
                'status' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $requestModel = RequestModel::findOrFail($request->request_id);

            foreach ($request->items as $item) {
                $stockIn = StockIn::findOrFail($item['item_id']);
                $category = $stockIn->item->category;

                // Create StockOut entry
                $stockOut = StockOut::create([
                    'request_id' => $request->request_id,
                    'quantity' => $item['quantity'],
                    'date' => $request->date,
                    'status' => $category->name === 'Packages' ? 'Finished' : $request->status,
                ]);

                Log::info('StockOut created', ['stock_out_id' => $stockOut->id]);

                // If category is Packages, create PackageStock entry
                if ($category->name === 'Packages') {
                    PackageStock::create([
                        'stock_out_id' => $stockOut->id,
                        'item_name' => $stockIn->item->name,
                        'category' => $category->name,
                        'type' => $stockIn->item->type->name,
                        'capacity' => $stockIn->item->capacity,
                        'unit' => $stockIn->item->unit,
                        'quantity' => $item['quantity'],
                    ]);

                    Log::info('PackageStock created', ['stock_out_id' => $stockOut->id]);
                }

                // Decrement the stock
                $stockIn->decrement('quantity', $item['quantity']);
            }

            // Update request status
            $requestModel->update(['status' => 'Approved']);

            DB::commit();
            Log::info('Stock out recorded successfully');

            return response()->json(['message' => 'Stock out recorded successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record stock out', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
