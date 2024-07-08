<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Stock In",
 *     description="API Endpoints for Stock In"
 * )
 */
class StockInController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stock-ins",
     *     summary="Get all stock in entries",
     *     tags={"Stock In"},
     *     @OA\Response(
     *         response=200,
     *         description="List of stock in entries",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/StockIn"))
     *     )
     * )
     */
    public function index()
    {
        $stockIns = StockIn::orderBy('id', 'desc')->get();
        return response()->json($stockIns);
    }

    /**
     * @OA\Post(
     *     path="/api/stock-ins",
     *     summary="Create a new stock in entry",
     *     tags={"Stock In"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockInRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock in entry created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/StockIn"))
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
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:1',
            'date_received' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::create($request->all());

        return response()->json(['message' => 'Stock in entry created successfully', 'data' => $stockIn], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/stock-ins/{id}",
     *     summary="Get a stock in entry by ID",
     *     tags={"Stock In"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock In ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/StockIn")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock In entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function show(string $id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        return response()->json($stockIn);
    }

    /**
     * @OA\Put(
     *     path="/api/stock-ins/{id}",
     *     summary="Update a stock in entry",
     *     tags={"Stock In"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock In ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockInRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock In entry updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/StockIn"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock In entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|exists:items,id',
            'quantity' => 'sometimes|required|numeric|min:1',
            'date_received' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        $stockIn->update($request->all());

        return response()->json(['message' => 'Stock In entry updated successfully', 'data' => $stockIn], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/stock-ins/{id}",
     *     summary="Delete a stock in entry",
     *     tags={"Stock In"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock In ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock In entry deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock In entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $stockIn = StockIn::find($id);

        if (is_null($stockIn)) {
            return response()->json(['message' => 'Stock In entry not found'], 404);
        }

        $stockIn->delete();

        return response()->json(['message' => 'Stock In entry deleted successfully'], 200);
    }
}
