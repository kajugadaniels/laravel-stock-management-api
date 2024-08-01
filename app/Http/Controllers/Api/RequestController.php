<?php

namespace App\Http\Controllers\API;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function index()
    {
        try {
            $requests = RequestModel::with([
                'items.item',
                'contactPerson',
                'requestFor'
            ])
            ->orderBy('id', 'desc')
            ->get();

            return response()->json($requests);
        } catch (\Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'required|integer|exists:employees,id',
            'requester_name' => 'required|string|max:255',
            'request_from' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'note' => 'nullable|string',
            'request_for_id' => 'nullable|integer|exists:items,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $request_for_id = $request->get('request_for_id', 0);

        try {
            DB::beginTransaction();

            $totalQuantity = collect($request->items)->sum('quantity');

            $requestModel = RequestModel::create([
                'contact_person_id' => $request->contact_person_id,
                'requester_name' => $request->requester_name,
                'request_from' => $request->request_from,
                'status' => $request->status,
                'note' => $request->note,
                'request_for_id' => $request_for_id,
                'quantity' => $totalQuantity,
            ]);

            foreach ($request->items as $item) {
                $requestModel->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);
            }

            DB::commit();

            Log::info('Request created successfully:', $requestModel->toArray());
            return response()->json(['message' => 'Request created successfully', 'data' => $requestModel->load('items')], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('QueryException:', ['message' => $e->getMessage(), 'sql' => $e->getSql(), 'bindings' => $e->getBindings()]);
            return response()->json(['message' => 'Database Error', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $requestModel = RequestModel::with([
                'items' => function ($query) {
                    $query->with([
                        'item.type',
                        'item.category',
                        'item' => function ($itemQuery) {
                            $itemQuery->select('id', 'name', 'capacity', 'unit');
                        },
                        'supplier'
                    ]);
                },
                'contactPerson',
                'requestFor'
            ])->find($id);

            if (is_null($requestModel)) {
                return response()->json(['message' => 'Request not found'], 404);
            }

            return response()->json($requestModel);
        } catch (\Exception $e) {
            Log::error('Error in show method: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'sometimes|required|integer|exists:employees,id',
            'requester_name' => 'sometimes|required|string|max:255',
            'request_from' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'note' => 'nullable|string',
            'request_for_id' => 'nullable|integer|exists:items,id',
            'items' => 'sometimes|required|array',
            'items.*.item_id' => 'required_with:items|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestModel = RequestModel::find($id);

        if (is_null($requestModel)) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        $request_for_id = $request->get('request_for_id', 0);

        $requestModel->update(array_merge($request->only([
            'contact_person_id',
            'requester_name',
            'request_from',
            'status',
            'note',
        ]), ['request_for_id' => $request_for_id]));

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
        $items = Item::with('category')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Finished');
            })->get();

        return response()->json($items);
    }

    public function getRawMaterialItems()
    {
        $items = DB::table('stock_ins')
            ->join('items', 'stock_ins.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('types', 'items.type_id', '=', 'types.id')
            ->join('suppliers', 'stock_ins.supplier_id', '=', 'suppliers.id')
            ->where('categories.name', 'Raw Materials')
            ->where('stock_ins.quantity', '>', 0)
            ->select(
                'stock_ins.id',
                'items.name',
                'items.type_id',
                'types.name as type_name',
                'stock_ins.supplier_id',
                'suppliers.name as supplier_name',
                'stock_ins.quantity'
            )
            ->get();

        return response()->json($items);
    }

    public function getPackageItems()
    {
        $items = DB::table('stock_ins')
            ->join('items', 'stock_ins.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('types', 'items.type_id', '=', 'types.id')
            ->join('suppliers', 'stock_ins.supplier_id', '=', 'suppliers.id')
            ->where('categories.name', 'Packages')
            ->where('stock_ins.quantity', '>', 0)
            ->select(
                'stock_ins.id',
                'items.name',
                'items.type_id',
                'types.name as type_name',
                'stock_ins.supplier_id',
                'suppliers.name as supplier_name',
                'stock_ins.quantity',
                'items.capacity',
                'items.unit'
            )
            ->get();

        return response()->json($items);
    }
}
