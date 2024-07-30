<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOut;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProcessController extends Controller
{
    public function getDetailedStockOuts()
    {
        $stockOuts = StockOut::with([
            'request.items.item',
            'request.items.item.category',
            'request.items.item.type',
            'request.contactPerson',
            'request.requestFor'
        ])
        ->whereHas('request', function($query) {
            $query->where('request_from', 'Production');
        })
        ->orderBy('id', 'desc')
        ->get();

        return response()->json($stockOuts);
    }
}
