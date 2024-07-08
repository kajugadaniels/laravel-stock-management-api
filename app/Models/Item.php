<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }
}
