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
            'request.item.item',
            'request.item.item.category',
            'request.item.item.type',
            'request.contactPerson',
            'request.requestFor'
        ])->orderBy('id', 'desc')->get();

        return response()->json($stockOuts);
    }
}
