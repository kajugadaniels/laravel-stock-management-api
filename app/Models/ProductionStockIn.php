<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionStockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_product',
        'package_type',
        'quantity',
        'total_sacks',
        'remaining_kg',
        'remaining_sacks',
    ];
}

