<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_out_id',
        'item_name',
        'category',
        'type',
        'capacity',
        'unit',
        'quantity',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }
}
