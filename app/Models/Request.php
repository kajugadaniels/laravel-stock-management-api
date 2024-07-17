<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'item_id',
        'contact_id', 
        'requester',
        'request_from',
        'status',
        'request_for',
        'qty',
        'note'
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'contact_id');
    }

    public function requestForItem()
    {
        return $this->belongsTo(Item::class, 'request_for');
    }
}
