<?php

namespace App\Http\Controllers\Api;

use App\Models\PackageStock;
use Illuminate\Http\Request;
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

        $packageStock = PackageStock::create($validatedData);
        return response()->json($packageStock, 201);
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
