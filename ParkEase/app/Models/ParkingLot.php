<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'owner_id',
    'name',
    'address',
    'pincode',
    'city',
    'latitude',
    'longitude',
    'price_per_slot',
    'layout_type',
    'total_rows',
    'slots_per_row'
])]
class ParkingLot extends Model
{
    protected $collection = 'parking_lots';

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'parking_lot_id');
    }
}
