<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use App\Models\PackageStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PackageStockController extends Controller
{
    public function index()
    {
        $packageStocks = PackageStock::all();
        return response()->json($packageStocks);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'stock_out_id' => 'required|exists:stock_outs,id',
            'item_name' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|string',
            'capacity' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'quantity' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            // Create the new PackageStock
            $packageStock = PackageStock::create($validatedData);

            // Update the related StockOut status to 'Finished'
            $stockOut = StockOut::findOrFail($validatedData['stock_out_id']);
            $stockOut->update(['status' => 'Finished']);

            DB::commit();

            return response()->json($packageStock, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create package stock and update stock out status', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(PackageStock $packageStock)
    {
        return response()->json($packageStock);
    }

    public function update(Request $request, PackageStock $packageStock)
    {
        $validatedData = $request->validate([
            'stock_out_id' => 'sometimes|required|exists:stock_outs,id',
            'item_name' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'capacity' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'quantity' => 'sometimes|required|integer',
        ]);

        $packageStock->update($validatedData);
        return response()->json($packageStock);
    }

    public function destroy(PackageStock $packageStock)
    {
        $packageStock->delete();
        return response()->json(null, 204);
    }
}
