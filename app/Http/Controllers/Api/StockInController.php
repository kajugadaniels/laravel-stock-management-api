<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = StockIn::with(['supplier', 'item.category', 'item.type', 'employee']);

            // Handle category filter
            if (!empty($request->category)) {
                $query->whereHas('item', function ($q) use ($request) {
                    $q->where('category_id', $request->category)
                      ->where('name', '!=', 'Finished');
                });
            } else {
                $query->whereHas('item.category', function ($q) {
                    $q->where('name', '!=', 'Finished');
                });
            }

            // Handle type filter
            if (!empty($request->type)) {
                $query->whereHas('item', function ($q) use ($request) {
                    $q->where('type_id', $request->type);
                });
            }

            // Handle date filters
            if (!empty($request->startDate)) {
                $query->where('date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('date', '<=', $request->endDate);
            }

            // Handle loading_payment_status filter
            if ($request->filled('loading_payment_status')) {
                $status = filter_var($request->loading_payment_status, FILTER_VALIDATE_BOOLEAN);
                $query->where('loading_payment_status', $status);
            }

            $stockIns = $query->get();
            return response()->json($stockIns);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error fetching stock ins: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer',
            'plate_number' => 'required|string',
            'batch_number' => 'nullable|string',
            'comment' => 'nullable|string',
            'date' => 'required|date',
            'registered_by' => 'required|exists:employees,id',
            'loading_payment_status' => 'required|boolean'
        ]);

        $batchNumber = $request->batch_number ?? 'BAT' . random_int(1000, 9999);

        $stockIns = [];
        foreach ($request->items as $item) {
            $stockIn = StockIn::create([
                'supplier_id' => $request->supplier_id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'plate_number' => $request->plate_number,
                'batch_number' => $batchNumber,
                'comment' => $request->comment,
                'date' => $request->date,
                'registered_by' => $request->registered_by,
                'loading_payment_status' => $request->loading_payment_status,
            ]);
            $stockIns[] = $stockIn;
        }

        return response()->json($stockIns, 201);
    }

    public function show($id)
    {
        $stockIn = StockIn::with(['supplier', 'item.category', 'item.type', 'employee'])->findOrFail($id);
        return response()->json($stockIn);
    }

    public function update(Request $request, $id)
    {
        $stockIn = StockIn::findOrFail($id);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'plate_number' => 'required|string',
            'batch_number' => 'nullable|string',
            'comment' => 'nullable|string',
            'date' => 'required|date',
            'registered_by' => 'required|exists:employees,id',
            'loading_payment_status' => 'required|boolean'
        ]);

        $batchNumber = $request->batch_number ?? $stockIn->batch_number;

        $stockIn->update([
            'supplier_id' => $request->supplier_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'plate_number' => $request->plate_number,
            'batch_number' => $batchNumber,
            'comment' => $request->comment,
            'date' => $request->date,
            'registered_by' => $request->registered_by,
            'loading_payment_status' => $request->loading_payment_status,
        ]);

        return response()->json($stockIn);
    }

    public function destroy($id)
    {
        $stockIn = StockIn::findOrFail($id);

        // Check if there are associated requests
        if ($stockIn->requests()->exists()) {
            return response()->json(['message' => 'You cannot delete the record because it is used in request.'], 400);
        }

        $stockIn->delete();

        return response()->json(['message' => 'Stock In deleted successfully.'], 204);
    }

    public function getItemsBySupplier($supplierId)
    {
        $items = DB::table('supplier_items')
            ->join('items', 'supplier_items.item_id', '=', 'items.id')
            ->select('items.id', 'items.name')
            ->where('supplier_items.supplier_id', $supplierId)
            ->orderBy('items.name')
            ->get();

        return response()->json($items);
    }
}
