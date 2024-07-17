<?php

namespace App\Http\Controllers\API;

use App\Models\Request as RequestModel; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = RequestModel::orderBy('id', 'desc')->get();
        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:items,id',
            'contact_id' => 'required|integer|exists:employees,id',
            'requester' => 'required|string|max:255',
            'request_from' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'request_for' => 'required|integer|exists:items,id',
            'qty' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::create($request->all());

        return response()->json(['message' => 'Request created successfully', 'data' => $requestModel], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        return response()->json($requestModel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'sometimes|required|integer|exists:items,id',
            'contact_id' => 'sometimes|required|integer|exists:employees,id',
            'requester' => 'sometimes|required|string|max:255',
            'request_from' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'request_for' => 'sometimes|required|integer|exists:items,id',
            'qty' => 'sometimes|required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $requestModel->update($request->all());

        return response()->json(['message' => 'Request updated successfully', 'data' => $requestModel], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $requestModel->delete();

        return response()->json(['message' => 'Request deleted successfully'], 200);
    }
}
