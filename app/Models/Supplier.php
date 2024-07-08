<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public function items()
    {
        return $this->belongsTo(Item::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }
}
