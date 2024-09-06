<?php

namespace App\Http\Controllers\Api;

use App\Models\StockIn;
use App\Models\Category;
use App\Models\RequestItem;
use Illuminate\Http\Request;
use App\Models\ProductStockIn;
use App\Models\ProductStockOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stockIns = StockIn::with(['item', 'item.category', 'item.type'])
                ->selectRaw('item_id, SUM(init_qty) as total_stock_in')
                ->groupBy('item_id')
                ->when($request->filled('category'), function ($query) use ($request) {
                    $query->whereHas('item.category', function ($q) use ($request) {
                        $q->where('id', $request->category);
                    });
                })
                ->when($request->filled('type'), function ($query) use ($request) {
                    $query->whereHas('item.type', function ($q) use ($request) {
                        $q->where('id', $request->type);
                    });
                })
                ->when($request->filled('name'), function ($query) use ($request) {
                    $query->whereHas('item', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->name . '%');
                    });
                })
                ->orderBy('total_stock_in', 'desc')
                ->simplePaginate($request->input('itemsPerPage', 10));

            $inventory = $stockIns->map(function ($stockIn) {
                $totalStockOut = $this->getTotalStockOut($stockIn->item_id);
                return [
                    'id' => $stockIn->item_id,
                    'name' => $stockIn->item->name,
                    'category_name' => $stockIn->item->category->name,
                    'type_name' => $stockIn->item->type->name,
                    'capacity' => $stockIn->item->capacity,
                    'unit' => $stockIn->item->unit,
                    'total_stock_in' => $stockIn->total_stock_in,
                    'total_stock_out' => $totalStockOut,
                    'available_quantity' => max(0, $stockIn->total_stock_in - $totalStockOut),
                ];
            });

            return response()->json($inventory);
        } catch (\Exception $e) {
            Log::error('Error in InventoryController@index', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    private function getTotalStockOut($itemId)
    {
        return RequestItem::whereHas('stockIn', function ($query) use ($itemId) {
            $query->where('item_id', $itemId);
        })
        // ->whereHas('request.stockOut', function ($query) {
        //     $query->where('status', 'Finished');
        // })
        ->sum('quantity');
    }

    public function productionInventory(Request $request)
    {
        try {
            $productStockIns = ProductStockIn::select('item_name', 'package_type')
                ->selectRaw('SUM(item_qty) as total_stock_in, SUM(quantity) as total_packages')
                ->groupBy('item_name', 'package_type')
                ->when($request->filled('item_name'), function ($query) use ($request) {
                    $query->where('item_name', 'like', '%' . $request->item_name . '%');
                })
                ->orderBy('total_stock_in', 'desc')
                ->get();

            $inventory = $productStockIns->map(function ($stockIn) {
                $totalStockOut = $this->getProductTotalStockOut($stockIn->item_name, $stockIn->package_type);
                return [
                    'item_name' => $stockIn->item_name,
                    'package_type' => $stockIn->package_type,
                    'total_stock_in' => $stockIn->total_stock_in,
                    'total_packages_in' => $stockIn->total_packages,
                    'total_stock_out' => $totalStockOut,
                    'available_quantity' => $stockIn->total_packages - $totalStockOut,
                ];
            });

            return response()->json($inventory);
        } catch (\Exception $e) {
            Log::error('Error in InventoryController@productionInventory', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    private function getProductTotalStockOut($itemName, $packageType)
    {
        return ProductStockOut::where('item_name', $itemName)
            ->whereHas('productStockIn', function ($query) use ($packageType) {
                $query->where('package_type', $packageType);
            })
            ->sum('quantity');
    }

    public function rawMaterials()
    {
        try {
            DB::enableQueryLog();

            $rawMaterialsCategory = Category::where('name', 'Raw Materials')->first();

            if (!$rawMaterialsCategory) {
                Log::warning('Raw Materials category not found');
                return response()->json(['message' => 'Raw Materials category not found'], 404);
            }

            $stockIn = StockIn::whereHas('item.category', function ($query) use ($rawMaterialsCategory) {
                $query->where('id', $rawMaterialsCategory->id);
            })->sum('init_qty');

            $stockOut = RequestItem::whereHas('stockIn.item.category', function ($query) use ($rawMaterialsCategory) {
                $query->where('id', $rawMaterialsCategory->id);
            })->sum('quantity');

            Log::info('Raw Materials Inventory', [
                'category_id' => $rawMaterialsCategory->id,
                'stockIn' => $stockIn,
                'stockOut' => $stockOut,
                'queries' => DB::getQueryLog(),
            ]);

            return response()->json([
                'stockIn' => $stockIn,
                'stockOut' => $stockOut,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in InventoryController@rawMaterials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'queries' => DB::getQueryLog(),
            ]);
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function packages()
    {
        try {
            DB::enableQueryLog();

            $packagesCategory = Category::where('name', 'Packages')->first();

            if (!$packagesCategory) {
                Log::warning('Packages category not found');
                return response()->json(['message' => 'Packages category not found'], 404);
            }

            $stockIn = StockIn::whereHas('item.category', function ($query) use ($packagesCategory) {
                $query->where('id', $packagesCategory->id);
            })->sum('init_qty');

            $stockOut = RequestItem::whereHas('stockIn.item.category', function ($query) use ($packagesCategory) {
                $query->where('id', $packagesCategory->id);
            })->sum('quantity');

            Log::info('Packages Inventory', [
                'category_id' => $packagesCategory->id,
                'stockIn' => $stockIn,
                'stockOut' => $stockOut,
                'queries' => DB::getQueryLog(),
            ]);

            return response()->json([
                'stockIn' => $stockIn,
                'stockOut' => $stockOut,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in InventoryController@packages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'queries' => DB::getQueryLog(),
            ]);
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
}
