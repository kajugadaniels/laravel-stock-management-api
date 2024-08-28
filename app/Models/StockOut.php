<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'quantity',
        'package_qty',
        'date',
        'status',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
