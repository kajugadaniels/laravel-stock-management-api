<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_items');
    }
}
