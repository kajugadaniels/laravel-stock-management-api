<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    protected $fillable = [
        'supplier_id',
        'item_id',
        'quantity',
        'plate_number',
        'batch_number',
        'comment',
        'date',
        'registered_by',
        'loading_payment_status'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
