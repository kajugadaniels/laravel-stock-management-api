<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::with([
            'supplierItem',
            'supplierItem.supplier',
            'supplierItem.item'
        ])->get();

        $stockIns = $stockIns->map(function($stockIn) {
            return [
                'id' => $stockIn->id,
                'supplier_name' => $stockIn->supplierItem->supplier->name,
                'item_name' => $stockIn->supplierItem->item->name,
                'item_category' => $stockIn->supplierItem->item->category_name,
                'item_type' => $stockIn->supplierItem->item->type_name,
                'quantity' => $stockIn->quantity,
                'plate_number' => $stockIn->plate_number,
                'batch_number' => $stockIn->batch_number,
                'created_at' => $stockIn->created_at->toDateTimeString()
            ];
        });

        return response()->json($stockIns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_item_id' => 'required|exists:supplier_items,id',
            'quantity' => 'required|integer',
            'plate_number' => 'required|string',
            'batch_number' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        $batchNumber = $request->batch_number ?? 'BAT' . random_int(1000, 9999);

        $stockIn = StockIn::create([
            'supplier_item_id' => $request->supplier_item_id,
            'quantity' => $request->quantity,
            'plate_number' => $request->plate_number,
            'batch_number' => $batchNumber,
            'comment' => $request->comment,
        ]);

        return response()->json($stockIn, 201);
    }

    public function show($id)
    {
        $stockIn = StockIn::with([
            'supplierItem',
            'supplierItem.supplier',
            'supplierItem.item'
        ])->findOrFail($id);

        $response = [
            'id' => $stockIn->id,
            'supplier_name' => $stockIn->supplierItem->supplier->name,
            'item_name' => $stockIn->supplierItem->item->name,
            'item_category' => $stockIn->supplierItem->item->category_name,
            'item_type' => $stockIn->supplierItem->item->type_name,
            'quantity' => $stockIn->quantity,
            'plate_number' => $stockIn->plate_number,
            'batch_number' => $stockIn->batch_number,
            'created_at' => $stockIn->created_at->toDateTimeString()
        ];

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $stockIn = StockIn::findOrFail($id);

        $request->validate([
            'supplier_item_id' => 'required|exists:supplier_items,id',
            'quantity' => 'required|integer',
            'plate_number' => 'required|string',
            'batch_number' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        $batchNumber = $request->batch_number ?? $stockIn->batch_number;

        $stockIn->update([
            'supplier_item_id' => $request->supplier_item_id,
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
}
