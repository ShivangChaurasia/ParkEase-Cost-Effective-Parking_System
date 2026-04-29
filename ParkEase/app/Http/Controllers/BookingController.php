<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\Slot;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function getSlots(Request $request, $parkingLotId)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|string',
        ]);

        $slots = Slot::where('parking_lot_id', $parkingLotId)->get();
        
        $bookedSlotIds = Booking::where('parking_lot_id', $parkingLotId)
            ->where('date', $validated['date'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('slot_id')
            ->toArray();

        $slotsData = $slots->map(function ($slot) use ($bookedSlotIds) {
            $slot->is_booked = in_array($slot->_id, $bookedSlotIds);
            return $slot;
        });

        return response()->json(['slots' => $slotsData]);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'parking_lot_id' => 'required|string',
            'slot_id' => 'required|string',
            'time_slot_id' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $parkingLot = ParkingLot::findOrFail($validated['parking_lot_id']);
        $slot = Slot::where('_id', $validated['slot_id'])->where('parking_lot_id', $parkingLot->_id)->firstOrFail();

        // Core Double-Booking Prevention Logic
        $existingBooking = Booking::where('parking_lot_id', $parkingLot->_id)
            ->where('slot_id', $slot->_id)
            ->where('date', $validated['date'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'message' => 'This slot is already booked for the selected date and time.',
            ], 409); // Conflict
        }

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'parking_lot_id' => $parkingLot->_id,
            'slot_id' => $slot->_id,
            'time_slot_id' => $validated['time_slot_id'],
            'date' => $validated['date'],
            'price' => $parkingLot->price_per_slot,
            'status' => 'confirmed',
            'booking_id' => strtoupper(Str::random(10)),
        ]);

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'booking' => $booking
        ], 201);
    }
}
