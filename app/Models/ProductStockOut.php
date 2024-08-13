<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockOut extends Model
{
    use HasFactory;

    protected $table = 'production_stock_out';

    protected $fillable = [
        'prod_stock_in_id',
        'employee_id',
        'location',
        'plate',
        'contact',
        'loading_payment_status',
        'quantity',
        'comment',
        'batch',
        'client_name',
        'item_name'
    ];

    public function productStockIn()
    {
        return $this->belongsTo(ProductStockIn::class, 'prod_stock_in_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
