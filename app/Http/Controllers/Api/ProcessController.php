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

    public function getUnmergedPackageStockOuts()
    {
        $packages = DB::table('stock_outs')
            ->join('requests', 'stock_outs.request_id', '=', 'requests.id')
            ->join('request_items', 'requests.id', '=', 'request_items.request_id')
            ->join('items', 'request_items.stock_in_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->join('types', 'items.type_id', '=', 'types.id')
            ->select(
                'stock_outs.id as stock_out_id',
                'requests.id as request_id',
                'items.id as item_id',
                'items.name as item_name',
                'items.capacity',
                'items.unit',
                'categories.name as category_name',
                'types.name as type_name',
                'request_items.quantity as requested_quantity',
                'stock_outs.quantity as stock_out_quantity',
                'stock_outs.status',
                'stock_outs.date'
            )
            ->where('requests.request_from', 'Production')
            ->where('categories.name', 'Packages')
            ->orderBy('stock_outs.id', 'desc')
            ->get();

        return response()->json($packages);
    }
}
