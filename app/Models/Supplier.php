<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact', 'address'];

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'supplier_items');
    }
}
