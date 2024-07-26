<?php

namespace App\Http\Controllers\API;

use App\Models\Request as RequestModel;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = RequestModel::with(['items', 'contactPerson'])->orderBy('id', 'desc')->get();
        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'required|integer|exists:employees,id',
            'requester_name' => 'required|string|max:255',
            'request_from' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required|integer|min:1',
            'request_for_id' => 'required|integer|exists:items,id'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        try {
            // Set initial quantity to 0
            $requestModel = RequestModel::create($request->only([
                'contact_person_id',
                'requester_name',
                'request_from',
                'status',
                'note',
                'request_for_id'
            ]) + ['quantity' => 0]);

            $totalQuantity = 0;
            foreach ($request->items as $item) {
                $totalQuantity += $item['quantity'];
                Log::info('Attaching item to request:', ['stock_in_id' => $item['item_id'], 'quantity' => $item['quantity']]);
                $requestModel->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);
            }

            // Update the total quantity
            $requestModel->update(['quantity' => $totalQuantity]);

            Log::info('Request created successfully:', $requestModel->toArray());
            return response()->json(['message' => 'Request created successfully', 'data' => $requestModel->load('items')], 201);
        } catch (QueryException $e) {
            Log::error('QueryException:', ['message' => $e->getMessage(), 'sql' => $e->getSql(), 'bindings' => $e->getBindings()]);
            return response()->json(['message' => 'Database Error', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $requestModel = RequestModel::with(['items', 'contactPerson'])->find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        return response()->json($requestModel);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'sometimes|required|integer|exists:employees,id',
            'requester_name' => 'sometimes|required|string|max:255',
            'request_from' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'note' => 'nullable|string',
            'items' => 'sometimes|required|array',
            'items.*.item_id' => 'required_with:items|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'request_for_id' => 'sometimes|required|integer|exists:items,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $requestModel->update($request->only([
            'contact_person_id',
            'requester_name',
            'request_from',
            'status',
            'note',
            'request_for_id'
        ]));

        if ($request->has('items')) {
            $totalQuantity = 0;
            $requestModel->items()->detach();
            foreach ($request->items as $item) {
                $totalQuantity += $item['quantity'];
                $requestModel->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);
            }
            $requestModel->update(['quantity' => $totalQuantity]);
        }

        return response()->json(['message' => 'Request updated successfully', 'data' => $requestModel->load('items')], 200);
    }

    public function destroy(string $id)
    {
        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $requestModel->delete();

        return response()->json(['message' => 'Request deleted successfully'], 200);
    }

    public function getFinishedItems()
    {
        $items = Item::whereHas('category', function ($query) {
            $query->where('name', 'Finished');
        })->get();

        return response()->json($items);
    }
}
