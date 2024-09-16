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
        // Log the request parameters
        Log::info('Index method called', ['request' => $request->all()]);

        // Build the query to retrieve StockOut records with related data
        $stockOuts = StockOut::with([
            'request.items' => function ($query) {
                $query->with([
                    'item' => function ($itemQuery) {
                        $itemQuery->select('id', 'name', 'capacity', 'unit', 'category_id', 'type_id');
                    },
                    'item.category:id,name',
                    'item.type:id,name',
                    'supplier:id,name'
                ]);
            }
        ])
        ->select('request_id', 'request_item_id', 'quantity','package_qty', 'created_at')
        ->get()
        ->groupBy('request_id');

        // Prepare an array to hold quantities by request_id and item_id
        $quantitiesByRequestItem = [];

        // Aggregate quantities
        foreach ($stockOuts as $requestId => $stockOutGroup) {
            foreach ($stockOutGroup as $stockOut) {
                $itemId = $stockOut->request_item_id;
                if (!isset($quantitiesByRequestItem[$requestId])) {
                    $quantitiesByRequestItem[$requestId] = [];
                }
                if (!isset($quantitiesByRequestItem[$requestId][$itemId])) {
                    $quantitiesByRequestItem[$requestId][$itemId] = 0;
                }
                $quantitiesByRequestItem[$requestId][$itemId] += $stockOut->quantity;
            }
        }

        // Attach quantities and item details to request items
        $stockOuts->transform(function ($stockOutGroup) use ($quantitiesByRequestItem) {
            return $stockOutGroup->map(function ($stockOut) use ($quantitiesByRequestItem) {
                $itemId = $stockOut->request_item_id;
                $requestId = $stockOut->request_id;
                $stockOut->approved_quantity = $quantitiesByRequestItem[$requestId][$itemId] ?? 0;

                // Attach item details
                $item = $stockOut->request->items->firstWhere('id', $itemId)->item;
                if ($item) {
                    $stockOut->item_name = $item->name;
                    $stockOut->item_category = $item->category->name;
                    $stockOut->item_type = $item->type->name;
                    $stockOut->item_capacity = $item->capacity;
                    $stockOut->item_unit = $item->unit;
                } else {
                    $stockOut->item_name = 'N/A';
                    $stockOut->item_category = 'N/A';
                    $stockOut->item_type = 'N/A';
                    $stockOut->item_capacity = 'N/A';
                    $stockOut->item_unit = 'N/A';
                }

                return $stockOut;
            });
        });

        // Return the grouped data as JSON
        return response()->json($stockOuts);
    }

    private function mergeStockOutsByRequestId($stockOuts)
    {
        $grouped = $stockOuts->groupBy(function ($item) {
            return $item->request_id;
        });

        $merged = $grouped->map(function ($items, $requestId) {
            $firstItem = $items->first();
            $mergedItem = $firstItem->replicate();
            $mergedItem->request->items = $items->flatMap(function ($item) {
                return $item->request->items;
            })->groupBy(function ($item) {
                return $item->item_id;
            })->map(function ($items) {
                $first = $items->first();
                $totalQuantity = $items->sum(function ($item) {
                    return $item->pivot->quantity;
                });
                return (object) [
                    'item' => $first->item,
                    'pivot' => (object) ['quantity' => $totalQuantity],
                    'supplier' => $first->supplier
                ];
            })->values();

            $mergedItem->quantity = $items->sum('quantity');
            return $mergedItem;
        })->values();

        return $merged;
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
                'items.*.package_qty' => 'required|integer',
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

                // Create StockOut entry, including request_item_id
                $requestItem = DB::table('request_items') // Use the correct table name
                    ->where('request_id', $request->request_id)
                    ->where('stock_in_id', $item['item_id'])
                    ->first();

                if (!$requestItem) {
                    throw new \Exception('Request item not found for the given item and request.');
                }

                // Create StockOut entry, including request_item_id
                $stockOut = StockOut::create([
                    'request_id' => $request->request_id,
                    'request_item_id' => $requestItem->id, // Use the retrieved request_item_id
                    'quantity' => $item['quantity'],
                    'package_qty' => $item['package_qty'],
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
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()  // Include full request data for troubleshooting
            ]);

            return response()->json([
                'message' => 'Failed to record stock out',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
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

    public function cancel($id)
    {
        try {
            $request = RequestModel::findOrFail($id);
            $request->update(['status' => 'Cancelled']);

            Log::info('Request cancelled successfully', ['request_id' => $id]);
            return response()->json(['message' => 'Request cancelled successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to cancel request', [
                'request_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to cancel request', 'error' => $e->getMessage()], 500);
        }
    }
}
