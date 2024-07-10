<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public function prodStockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function prodStockOuts()
    {
        return $this->hasMany(StockOut::class);
    }
}
