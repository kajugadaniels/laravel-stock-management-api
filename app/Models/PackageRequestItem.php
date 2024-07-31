<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRequestItem extends Model {
    use HasFactory;

    protected $fillable = [
        'package_request_id',
        'stock_in_id',
        'quantity'
    ];

    public function packageRequest() {
        return $this->belongsTo(PackageRequest::class);
    }

    public function stockIn() {
        return $this->belongsTo(StockIn::class);
    }
}
