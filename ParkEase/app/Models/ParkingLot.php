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
    'car_price',
    'bike_price',
    'bus_price',
    'layout_type',
    'total_rows',
    'slots_per_row',
    'car_slots',
    'bike_slots',
    'bus_slots',
    'opening_time',
    'closing_time',
    'status',
    'removal_requested_at',
    'scheduled_removal_date',
    'removal_reason',
    'removed_at',
    'removed_by',
    'is_accepting_bookings'
])]
class ParkingLot extends Model
{
    protected $collection = 'parking_lots';

    protected $attributes = [
        'status' => 'active',
        'is_accepting_bookings' => true,
    ];

    protected $casts = [
        'is_accepting_bookings' => 'boolean',
        'removal_requested_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'parking_lot_id');
    }
}
