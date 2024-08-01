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
            'request.items.item' => function($query) {
                $query->with('category', 'type');
            },
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
        ->get()
        ->map(function ($stockOut) {
            $stockOut->unmergedItems = $stockOut->request->items->map(function ($item) {
                return [
                    'item_id' => $item->item->id,
                    'item_name' => $item->item->name,
                    'capacity' => $item->item->capacity,
                    'unit' => $item->item->unit,
                    'category' => $item->item->category->name,
                    'type' => $item->item->type->name,
                    'quantity' => $item->pivot->quantity,
                ];
            });
            return $stockOut;
        });

        return response()->json($stockOuts);
    }

    public function getUnmergedPackageStockOuts()
    {
        $stockOuts = StockOut::with([
            'request.items' => function($query) {
                $query->whereHas('item.category', function($query) {
                    $query->where('name', 'Packages');
                });
            },
            'request.items.item' => function($query) {
                $query->with('category', 'type');
            },
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
        ->get()
        ->map(function ($stockOut) {
            $stockOut->unmergedItems = $stockOut->request->items->map(function ($item) {
                return [
                    'item_id' => $item->item->id,
                    'item_name' => $item->item->name,
                    'capacity' => $item->item->capacity,
                    'unit' => $item->item->unit,
                    'category' => $item->item->category->name,
                    'type' => $item->item->type->name,
                    'quantity' => $item->pivot->quantity,
                ];
            });
            return $stockOut;
        });

        return response()->json($stockOuts);
    }
}
