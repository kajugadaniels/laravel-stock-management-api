<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Stock Out",
 *     description="API Endpoints for Stock Out"
 * )
 */
class StockOutController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/stock-outs",
     *     summary="Get all stock out entries",
     *     tags={"Stock Out"},
     *     @OA\Response(
     *         response=200,
     *         description="List of stock out entries",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/StockOut"))
     *     )
     * )
     */
    public function index()
    {
        $stockOuts = StockOut::orderBy('id', 'desc')->get();
        return response()->json($stockOuts);
    }

    /**
     * @OA\Post(
     *     path="/api/stock-outs",
     *     summary="Create a new stock out entry",
     *     tags={"Stock Out"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockOutRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock out entry created successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/StockOut"))
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
            'date_delivered' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::create($request->all());

        return response()->json(['message' => 'Stock out entry created successfully', 'data' => $stockOut], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/stock-outs/{id}",
     *     summary="Get a stock out entry by ID",
     *     tags={"Stock Out"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock Out ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/StockOut")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock Out entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function show(string $id)
    {
        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock Out entry not found'], 404);
        }

        return response()->json($stockOut);
    }

    /**
     * @OA\Put(
     *     path="/api/stock-outs/{id}",
     *     summary="Update a stock out entry",
     *     tags={"Stock Out"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock Out ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockOutRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock Out entry updated successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="data", ref="#/components/schemas/StockOut"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"), @OA\Property(property="errors", type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock Out entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|exists:items,id',
            'quantity' => 'sometimes|required|numeric|min:1',
            'date_delivered' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock Out entry not found'], 404);
        }

        $stockOut->update($request->all());

        return response()->json(['message' => 'Stock Out entry updated successfully', 'data' => $stockOut], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/stock-outs/{id}",
     *     summary="Delete a stock out entry",
     *     tags={"Stock Out"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Stock Out ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock Out entry deleted successfully",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock Out entry not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $stockOut = StockOut::find($id);

        if (is_null($stockOut)) {
            return response()->json(['message' => 'Stock Out entry not found'], 404);
        }

        $stockOut->delete();

        return response()->json(['message' => 'Stock Out entry deleted successfully'], 200);
    }
}
