<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use App\Models\PackageStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            // Check if an item with the same attributes already exists
            $existingPackageStock = PackageStock::where([
                'item_name' => $validatedData['item_name'],
                'category' => $validatedData['category'],
                'type' => $validatedData['type'],
                'capacity' => $validatedData['capacity'],
                'unit' => $validatedData['unit'],
            ])->first();

            if ($existingPackageStock) {
                // Update the quantity of the existing item
                $existingPackageStock->quantity += $validatedData['quantity'];
                $existingPackageStock->save();
                $packageStock = $existingPackageStock;
                $action = 'updated';
            } else {
                // Create a new PackageStock
                $packageStock = PackageStock::create($validatedData);
                $action = 'created';
            }

            // Update the related StockOut status to 'Finished'
            $stockOut = StockOut::findOrFail($validatedData['stock_out_id']);
            $stockOut->status = 'Finished';
            $stockOut->save();

            DB::commit();

            Log::info("PackageStock {$action} and StockOut status updated", [
                'package_stock_id' => $packageStock->id,
                'stock_out_id' => $stockOut->id,
                'new_status' => $stockOut->status,
                'action' => $action
            ]);

            return response()->json([
                'message' => "Package stock {$action} and stock out status updated successfully",
                'package_stock' => $packageStock,
                'stock_out' => $stockOut
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process package stock and update stock out status', [
                'error' => $e->getMessage(),
                'stock_out_id' => $validatedData['stock_out_id']
            ]);
            return response()->json([
                'message' => 'Failed to process package stock and update stock out status',
                'error' => $e->getMessage()
            ], 500);
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
