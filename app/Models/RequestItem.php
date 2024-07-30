<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $table = 'request_items';

    protected $fillable = [
        'request_id',
        'stock_in_id',
        'quantity',
    ];

    /**
     * Get the request that owns the RequestItem.
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * Get the stockIn associated with the RequestItem.
     */
    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }
}
