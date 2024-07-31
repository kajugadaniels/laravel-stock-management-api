<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PackageRequest;
use App\Models\PackageRequestItem;
use App\Models\StockIn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PackageRequestController extends Controller {
    public function index() {
        $packageRequests = PackageRequest::with(['items.stockIn', 'contactPerson', 'requestFor'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($packageRequests);
    }

    public function store(Request $request) {
        Log::info('Store method called', $request->all());

        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'required|integer|exists:employees,id',
            'requester_name' => 'required|string|max:255',
            'request_from' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'note' => 'nullable|string',
            'request_for_id' => 'nullable|integer|exists:items,id',
            'items' => 'required|array|min:1',
            'items.*.stock_in_id' => 'required|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $requestForId = $request->get('request_for_id', 0);

        try {
            DB::beginTransaction();

            $totalQuantity = collect($request->items)->sum('quantity');

            $packageRequest = PackageRequest::create([
                'contact_person_id' => $request->contact_person_id,
                'requester_name' => $request->requester_name,
                'request_from' => $request->request_from,
                'status' => $request->status,
                'note' => $request->note,
                'request_for_id' => $requestForId,
                'quantity' => $totalQuantity,
            ]);

            foreach ($request->items as $item) {
                PackageRequestItem::create([
                    'package_request_id' => $packageRequest->id,
                    'stock_in_id' => $item['stock_in_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Package request created successfully', 'data' => $packageRequest->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create package request:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create package request', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        Log::info('Show method called', ['id' => $id]);

        $packageRequest = PackageRequest::with(['items.stockIn', 'contactPerson', 'requestFor'])->find($id);

        if (is_null($packageRequest)) {
            return response()->json(['message' => 'Package request not found'], 404);
        }

        return response()->json($packageRequest);
    }

    public function update(Request $request, $id) {
        Log::info('Update method called', ['request_data' => $request->all(), 'package_request_id' => $id]);

        $validator = Validator::make($request->all(), [
            'contact_person_id' => 'sometimes|required|integer|exists:employees,id',
            'requester_name' => 'sometimes|required|string|max:255',
            'request_from' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'note' => 'nullable|string',
            'request_for_id' => 'nullable|integer|exists:items,id',
            'items' => 'sometimes|required|array',
            'items.*.stock_in_id' => 'required_with:items|integer|exists:stock_ins,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 400);
        }

        $packageRequest = PackageRequest::find($id);

        if (is_null($packageRequest)) {
            return response()->json(['message' => 'Package request not found'], 404);
        }

        $requestForId = $request->get('request_for_id', 0);

        DB::beginTransaction();

        try {
            $packageRequest->update(array_merge($request->only([
                'contact_person_id',
                'requester_name',
                'request_from',
                'status',
                'note',
            ]), ['request_for_id' => $requestForId]));

            if ($request->has('items')) {
                $totalQuantity = 0;
                $packageRequest->items()->delete();
                foreach ($request->items as $item) {
                    $totalQuantity += $item['quantity'];
                    PackageRequestItem::create([
                        'package_request_id' => $packageRequest->id,
                        'stock_in_id' => $item['stock_in_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
                $packageRequest->update(['quantity' => $totalQuantity]);
            }

            DB::commit();

            return response()->json(['message' => 'Package request updated successfully', 'data' => $packageRequest->load('items')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update package request:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update package request', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        Log::info('Destroy method called', ['id' => $id]);

        $packageRequest = PackageRequest::find($id);

        if (is_null($packageRequest)) {
            return response()->json(['message' => 'Package request not found'], 404);
        }

        DB::beginTransaction();

        try {
            $packageRequest->items()->delete();
            $packageRequest->delete();

            DB::commit();

            return response()->json(['message' => 'Package request deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete package request:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete package request', 'error' => $e->getMessage()], 500);
        }
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
                'stock_ins.quantity'
            )
            ->get();

        return response()->json($items);
    }
}
