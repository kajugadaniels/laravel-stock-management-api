<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;
use App\Models\ProductStockIn;
use App\Models\ProductStockOut;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $data = [
                'totalInventoryValue' => $this->calculateTotalInventoryValue(),
                'lowStockItems' => $this->getLowStockItems(),
                'totalStockIn' => $this->getTotalStockIn(),
                'totalStockOut' => $this->getTotalStockOut(),
                'recentStockIns' => $this->getRecentStockIns(),
                'recentStockOuts' => $this->getRecentStockOuts(),
                'topSellingItems' => $this->getTopSellingItems(),
                'inventoryTrends' => $this->getInventoryTrends(),
                'productionOverview' => $this->getProductionOverview(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in DashboardController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while fetching dashboard data'], 500);
        }
    }

    private function calculateTotalInventoryValue()
    {
        try {
            return StockIn::join('items', 'stock_ins.item_id', '=', 'items.id')
                ->select(DB::raw('COALESCE(SUM(stock_ins.quantity * items.price), 0) as total_value'))
                ->value('total_value');
        } catch (\Exception $e) {
            Log::error('Error calculating total inventory value: ' . $e->getMessage());
            return 0;
        }
    }

    private function getLowStockItems()
    {
        try {
            return Item::whereColumn('quantity', '<', 'reorder_level')->count();
        } catch (\Exception $e) {
            Log::error('Error getting low stock items: ' . $e->getMessage());
            return 0;
        }
    }

    private function getTotalStockIn()
    {
        try {
            return StockIn::sum('quantity');
        } catch (\Exception $e) {
            Log::error('Error getting total stock in: ' . $e->getMessage());
            return 0;
        }
    }

    private function getTotalStockOut()
    {
        try {
            return StockOut::sum('quantity');
        } catch (\Exception $e) {
            Log::error('Error getting total stock out: ' . $e->getMessage());
            return 0;
        }
    }

    private function getRecentStockIns()
    {
        try {
            return StockIn::with('item:id,name')
                ->select('id', 'item_id', 'quantity', 'created_at')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($stockIn) {
                    return [
                        'date' => $stockIn->created_at->format('Y-m-d'),
                        'item_name' => $stockIn->item->name,
                        'quantity' => $stockIn->quantity,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting recent stock ins: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentStockOuts()
    {
        try {
            return StockOut::with(['request.items.item' => function($query) {
                    $query->select('id', 'name');
                }])
                ->select('id', 'request_id', 'quantity', 'created_at')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($stockOut) {
                    return [
                        'date' => $stockOut->created_at->format('Y-m-d'),
                        'item_name' => $stockOut->request->items->first()->item->name ?? 'N/A',
                        'quantity' => $stockOut->quantity,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting recent stock outs: ' . $e->getMessage());
            return [];
        }
    }

    private function getTopSellingItems()
    {
        try {
            return ProductStockOut::join('product_stock_ins', 'product_stock_outs.prod_stock_in_id', '=', 'product_stock_ins.id')
                ->select('product_stock_ins.item_name', 'product_stock_ins.package_type as category', DB::raw('SUM(product_stock_outs.quantity) as totalSold'))
                ->groupBy('product_stock_ins.item_name', 'product_stock_ins.package_type')
                ->orderByDesc('totalSold')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting top selling items: ' . $e->getMessage());
            return [];
        }
    }

    private function getInventoryTrends()
    {
        try {
            $trends = StockIn::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)  // Limit to last 30 days
            ->get();

            return [
                'labels' => $trends->pluck('date'),
                'datasets' => [
                    [
                        'label' => 'Inventory Level',
                        'data' => $trends->pluck('total_quantity'),
                        'borderColor' => 'rgb(75, 192, 192)',
                        'tension' => 0.1
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error getting inventory trends: ' . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }

    private function getProductionOverview()
    {
        try {
            $production = ProductStockIn::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)  // Limit to last 30 days
            ->get();

            return [
                'labels' => $production->pluck('date'),
                'datasets' => [
                    [
                        'label' => 'Production Quantity',
                        'data' => $production->pluck('total_quantity'),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error getting production overview: ' . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }
}
