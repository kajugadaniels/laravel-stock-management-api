<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_product_id', 'item_name', 'item_qty', 'package_type', 'quantity', 'status'
    ];

    public function finishedProduct()
    {
        return $this->belongsTo(FinishedProduct::class, 'finished_product_id');
    }
}
