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

        // Group stock-outs by request_id
        $groupedStockOuts = $stockOuts->groupBy('request_id')->map(function ($group) {
            $firstItem = $group->first();
            return [
                'id' => $firstItem->id,
                'request_id' => $firstItem->request_id,
                'request' => $firstItem->request,
                'status' => $firstItem->status,
                'date' => $firstItem->date,
                'total_quantity' => $group->sum('quantity'),
                'items' => $group->pluck('request.items')->flatten()->unique('id')->values(),
            ];
        })->values();

        return response()->json($groupedStockOuts);
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
            $unmergedItems = $stockOut->request->items->map(function ($item) {
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

            $mergedItems = $unmergedItems->groupBy(function ($item) {
                return $item['item_name'] . '-' . $item['capacity'];
            })->map(function ($groupedItems) {
                return [
                    'item_id' => $groupedItems->first()['item_id'],
                    'item_name' => $groupedItems->first()['item_name'],
                    'capacity' => $groupedItems->first()['capacity'],
                    'unit' => $groupedItems->first()['unit'],
                    'category' => $groupedItems->first()['category'],
                    'type' => $groupedItems->first()['type'],
                    'quantity' => $groupedItems->sum('quantity'),
                ];
            })->values();

            $stockOut->unmergedItems = $mergedItems;
            return $stockOut;
        });

        return response()->json($stockOuts);
    }
}
