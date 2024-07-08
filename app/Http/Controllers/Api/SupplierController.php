<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Suppliers",
 *     description="API Endpoints for Suppliers"
 * )
 */
class SupplierController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/suppliers",
     *     summary="Get all suppliers",
     *     tags={"Suppliers"},
     *     @OA\Response(
     *         response=200,
     *         description="List of suppliers",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Supplier"))
     *     )
     * )
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get();
        return response()->json($suppliers);
    }

    /**
     * @OA\Post(
     *     path="/api/suppliers",
     *     summary="Create a new supplier",
     *     tags={"Suppliers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Supplier"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/suppliers/{id}",
     *     summary="Get a supplier by ID",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Supplier ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);

        if (is_null($supplier)) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        return response()->json($supplier);
    }

    /**
     * @OA\Put(
     *     path="/api/suppliers/{id}",
     *     summary="Update a supplier",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Supplier ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SupplierRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/Supplier"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/suppliers/{id}",
     *     summary="Delete a supplier",
     *     tags={"Suppliers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Supplier ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
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
