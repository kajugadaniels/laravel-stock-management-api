<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinishedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_out_id',
        'item_qty',
        'brand_qty',
        'dechet_qty',
        'comment',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function productStockIns()
    {
        return $this->hasMany(ProductStockIn::class);
    }
}
