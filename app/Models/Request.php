<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'contact_person_id',
        'requester_name',
        'request_from',
        'status',
        'request_for_id',
        'quantity',
        'note'
    ];

    public function item()
    {
        return $this->belongsTo(StockIn::class, 'item_id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(Employee::class, 'contact_person_id');
    }

    public function requestFor()
    {
        return $this->belongsTo(Item::class, 'request_for_id');
    }

    public function stockOut()
    {
        return $this->hasOne(StockOut::class);
    }

     // Define the relationship back to StockIn
     public function stockIn()
     {
         return $this->belongsTo(StockIn::class, 'item_id');
     }

    
}
