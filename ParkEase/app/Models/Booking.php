<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'parking_lot_id',
    'slot_id',
    'time_slot_id',
    'date',
    'price',
    'status',
    'booking_id',
    'customer_name',
    'customer_phone',
    'booking_email',
    'vehicle_type',
    'payment_status',
    'is_manual',
    'razorpay_payment_id',
    'razorpay_order_id',
    'payment_method',
    'invoice_path',
    'invoice_number',
    'generated_at',
    'refund_amount',
    'refund_status',
    'cancelled_at',
    'attended_at',
    'verified_by',
    'completed_at',
    'expired_at',
    'vehicle_number',
    'booking_start_datetime',
    'booking_end_datetime',
    'booking_duration_minutes',
    'extended_from_booking_id'
]) ]
class Booking extends Model
{
    protected $collection = 'bookings';

    protected $casts = [
        'booking_start_datetime' => 'datetime',
        'booking_end_datetime' => 'datetime',
        'generated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'attended_at' => 'datetime',
        'completed_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function getStartCarbon()
    {
        if ($this->booking_start_datetime) {
            return \Carbon\Carbon::parse($this->booking_start_datetime, 'Asia/Kolkata');
        }
        if (!$this->time_slot_id || !$this->date) return null;
        $times = explode('-', $this->time_slot_id);
        return \Carbon\Carbon::parse($this->date . ' ' . trim($times[0]), 'Asia/Kolkata');
    }

    public function getEndCarbon()
    {
        if ($this->booking_end_datetime) {
            return \Carbon\Carbon::parse($this->booking_end_datetime, 'Asia/Kolkata');
        }
        if (!$this->time_slot_id || !$this->date) return null;
        $times = explode('-', $this->time_slot_id);
        return \Carbon\Carbon::parse($this->date . ' ' . trim($times[1]), 'Asia/Kolkata');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parkingLot()
    {
        return $this->belongsTo(ParkingLot::class, 'parking_lot_id');
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'booking_id');
    }
}
