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

        $slots = Slot::where('parking_lot_id', $parkingLotId)
            ->select(['_id', 'slot_number', 'vehicle_type', 'row', 'column'])
            ->get();
        
        $bookedSlotIds = Booking::where('parking_lot_id', $parkingLotId)
            ->where('date', $validated['date'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('slot_id')
            ->map(fn($id) => (string)$id)
            ->toArray();

        $slotsData = $slots->map(function ($slot) use ($bookedSlotIds) {
            $id = (string)$slot->_id;
            return [
                'id' => $id,
                '_id' => $id,
                'slot_number' => $slot->slot_number,
                'vehicle_type' => $slot->vehicle_type,
                'is_booked' => in_array($id, $bookedSlotIds)
            ];
        });

        return response()->json(['slots' => $slotsData]);
    }

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'parking_lot_id' => 'required|string',
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'required|string',
            'time_slot_id' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
            'vehicle_type' => 'nullable|string',
            'email' => 'required|email',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string'
        ]);

        $parkingLot = ParkingLot::findOrFail($validated['parking_lot_id']);
        $bookings = [];

        foreach ($validated['slot_ids'] as $slotId) {
            $slot = Slot::where('_id', $slotId)
                ->where('parking_lot_id', $parkingLot->_id)
                ->first();

            if (!$slot) {
                return response()->json(['message' => 'Invalid slot selection.'], 400);
            }

            // Double-Booking Prevention
            $existingBooking = Booking::where('parking_lot_id', $parkingLot->_id)
                ->where('slot_id', $slot->_id)
                ->where('date', $validated['date'])
                ->where('time_slot_id', $validated['time_slot_id'])
                ->whereIn('status', ['confirmed', 'pending'])
                ->exists();

            if ($existingBooking) {
                return response()->json([
                    'message' => "Slot {$slot->slot_number} is already booked.",
                ], 409);
            }

            $price = match ($slot->vehicle_type) {
                'car' => $parkingLot->car_price,
                'bike' => $parkingLot->bike_price,
                'bus' => $parkingLot->bus_price,
                default => 0,
            };

            $userId = Auth::id();
            if (!$userId) {
                $existingUser = \App\Models\User::where('email', $validated['email'])->first();
                if ($existingUser) {
                    $userId = $existingUser->_id;
                }
            }

            $bookings[] = Booking::create([
                'user_id' => $userId,
                'booking_email' => $validated['email'],
                'parking_lot_id' => $parkingLot->_id,
                'slot_id' => $slot->_id,
                'time_slot_id' => $validated['time_slot_id'],
                'date' => $validated['date'],
                'price' => $price,
                'status' => 'confirmed',
                'payment_status' => 'paid', // Simulate paid
                'booking_id' => strtoupper(Str::random(10)),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'vehicle_type' => $slot->vehicle_type,
            ]);
        }

        return response()->json([
            'message' => 'Bookings confirmed successfully',
            'bookings' => $bookings
        ], 201);
    }
}
