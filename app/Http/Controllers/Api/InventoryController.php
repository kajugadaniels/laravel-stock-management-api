<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = DB::table('items')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('types', 'items.type_id', '=', 'types.id')
            ->leftJoin('stock_ins', 'items.id', '=', 'stock_ins.item_id')
            ->leftJoin('requests', 'items.id', '=', 'requests.request_for_id')
            ->leftJoin('stock_outs', 'requests.id', '=', 'stock_outs.request_id')
            ->select(
                'items.id',
                'items.name',
                'categories.name as category',
                'types.name as type',
                DB::raw('COALESCE(SUM(stock_ins.quantity), 0) as stock_in'),
                DB::raw('COALESCE(SUM(stock_outs.quantity), 0) as stock_out')
            )
            ->groupBy('items.id', 'items.name', 'categories.name', 'types.name')
            ->get()
            ->map(function ($item) {
                $remaining = $item->stock_in - $item->stock_out;
                $percentage = $item->stock_in > 0 ? ($item->stock_out / $item->stock_in) * 100 : 0;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'type' => $item->type,
                    'stockIn' => $item->stock_in,
                    'stockOut' => $item->stock_out,
                    'remaining' => $remaining,
                    'percentage' => round($percentage, 2)
                ];
            });

        return response()->json($inventory);
    }
}
