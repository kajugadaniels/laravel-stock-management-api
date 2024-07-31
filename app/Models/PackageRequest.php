<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageRequest extends Model {
    use HasFactory;

    protected $fillable = [
        'contact_person_id',
        'requester_name',
        'request_from',
        'status',
        'request_for_id',
        'quantity',
        'note'
    ];

    public function items() {
        return $this->hasMany(PackageRequestItem::class);
    }

    public function contactPerson() {
        return $this->belongsTo(Employee::class, 'contact_person_id');
    }

    public function requestFor() {
        return $this->belongsTo(Item::class, 'request_for_id');
    }
}
