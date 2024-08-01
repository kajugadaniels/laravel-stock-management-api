<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ProcessController extends Controller
{
    public function getDetailedStockOuts()
    {
        $stockOuts = StockOut::with([
            'request.items' => function($query) {
                $query->whereHas('item.category', function($query) {
                    $query->where('name', 'Raw Materials')
                            ->where('name', '!=', 'Packages');
                });
            },
            'request.items.item',
            'request.items.item.category',
            'request.items.item.type',
            'request.contactPerson',
            'request.requestFor'
        ])
        ->whereHas('request', function($query) {
            $query->where('request_from', 'Production')
                    ->whereHas('items.item.category', function($query) {
                        $query->where('name', 'Raw Materials')
                            ->where('name', '!=', 'Packages');
                });
        })
        ->orderBy('id', 'desc')
        ->get();

        return response()->json($stockOuts);
    }

    public function getDetailedPackageStockOuts()
    {
        $stockOuts = StockOut::with([
            'request.items' => function($query) {
                $query->whereHas('item.category', function($query) {
                    $query->where('name', 'Packages');
                });
            },
            'request.items.item',
            'request.items.item.category',
            'request.items.item.type',
            'request.contactPerson',
            'request.requestFor'
        ])
        ->whereHas('request', function($query) {
            $query->where('request_from', 'Production')
                    ->whereHas('items.item.category', function($query) {
                        $query->where('name', 'Packages');
                    });
        })
        ->orderBy('id', 'desc')
        ->get();

        return response()->json($stockOuts);
    }

    public function getDetailedPackageItems()
    {
        try {
            $packageItems = DB::table('request_items')
                ->join('items', 'request_items.item_id', '=', 'items.id')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->join('requests', 'request_items.request_id', '=', 'requests.id')
                ->where('categories.name', 'Packages')
                ->where('requests.request_from', 'Production')
                ->select(
                    'request_items.id',
                    'items.name',
                    'items.capacity',
                    'items.unit',
                    'request_items.quantity'
                )
                ->get();

            return response()->json($packageItems);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in getDetailedPackageItems method: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


}
