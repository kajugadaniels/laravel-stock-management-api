<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_item_id',
        'quantity',
        'plate_number',
        'batch_number',
        'comment'
    ];

    public function supplierItem()
    {
        return $this->belongsTo(SupplierItem::class);
    }
}
