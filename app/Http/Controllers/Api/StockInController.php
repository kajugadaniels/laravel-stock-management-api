<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::with(['supplier', 'item'])->get();
        return response()->json($stockIns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'plate_number' => 'required|string',
            'batch_number' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        $batchNumber = $request->batch_number ?? 'BAT' . random_int(1000, 9999);

        $stockIn = StockIn::create([
            'supplier_id' => $request->supplier_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'plate_number' => $request->plate_number,
            'batch_number' => $batchNumber,
            'comment' => $request->comment,
        ]);

        return response()->json($stockIn, 201);
    }

    public function show($id)
    {
        $stockIn = StockIn::with(['supplier', 'item'])->findOrFail($id);
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
        ]);

        $batchNumber = $request->batch_number ?? $stockIn->batch_number;

        $stockIn->update([
            'supplier_id' => $request->supplier_id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'plate_number' => $request->plate_number,
            'batch_number' => $batchNumber,
            'comment' => $request->comment,
        ]);

        return response()->json($stockIn);
    }

    public function destroy($id)
    {
        $stockIn = StockIn::findOrFail($id);
        $stockIn->delete();

        return response()->json(null, 204);
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
