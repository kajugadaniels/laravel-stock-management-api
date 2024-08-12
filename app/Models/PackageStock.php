<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'category',
        'type',
        'capacity',
        'unit',
        'quantity',
    ];
}
