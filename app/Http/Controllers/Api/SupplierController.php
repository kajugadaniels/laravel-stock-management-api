<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get();
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $supplier = Supplier::create($request->all());

        return response()->json(['message' => 'Supplier created successfully', 'data' => $supplier], 201);
    }

    public function show(string $id)
    {
        $supplier = Supplier::find($id);

        if (is_null($supplier)) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'contact' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $supplier = Supplier::find($id);

        if (is_null($supplier)) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->update($request->all());

        return response()->json(['message' => 'Supplier updated successfully', 'data' => $supplier], 200);
    }

    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (is_null($supplier)) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully'], 200);
    }
}
