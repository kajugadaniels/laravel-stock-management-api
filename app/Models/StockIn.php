<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
{
    use SoftDeletes;
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


     // Define the relationship to requests
     public function requests()
     {
         return $this->hasMany(Request::class, 'item_id');
     }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'registered_by');
    }
}
