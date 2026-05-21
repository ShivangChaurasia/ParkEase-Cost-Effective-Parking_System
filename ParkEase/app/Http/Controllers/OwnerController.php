<?php

namespace App\Http\Controllers;

use App\Models\ParkingLot;
use App\Models\Slot;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function storeParkingLot(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'car_price' => 'required|numeric|min:0',
            'bike_price' => 'required|numeric|min:0',
            'bus_price' => 'required|numeric|min:0',
            'opening_time' => 'required|string',
            'closing_time' => 'required|string',
            'car_slots' => 'required|integer|min:0',
            'bike_slots' => 'required|integer|min:0',
            'bus_slots' => 'required|integer|min:0',
        ]);

        $parkingLot = ParkingLot::create(array_merge($validated, [
            'owner_id' => Auth::id(),
            'layout_type' => 'dynamic',
        ]));

        $this->generateVehicleSlots($parkingLot, $validated['car_slots'], $validated['bike_slots'], $validated['bus_slots']);

        return response()->json([
            'message' => 'Parking lot created successfully',
            'parking_lot' => $parkingLot
        ]);
    }

    public function manageLot($id)
    {
        $parkingLot = ParkingLot::where('_id', $id)->where('owner_id', Auth::id())->firstOrFail();
        return view('owner.manage', compact('parkingLot'));
    }

    public function storeManualBooking(Request $request)
    {
        $validated = $request->validate([
            'parking_lot_id' => 'required|string',
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);
    
        // Past-Time Slot Booking Validation (Authoritative Backend Guard)
        $start = \Carbon\Carbon::parse($validated['start_datetime'], 'Asia/Kolkata');
        $end = \Carbon\Carbon::parse($validated['end_datetime'], 'Asia/Kolkata');
        $duration = $start->diffInMinutes($end);

        if ($start->isPast()) {
            \Illuminate\Support\Facades\Log::warning('Manual attempt by owner to book a past time slot.', [
                'user_id' => Auth::id(),
                'slot_ids' => $validated['slot_ids'],
                'attempted_datetime' => $start->toDateTimeString(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'This slot time has already passed.'
            ], 422);
        }

        $parkingLot = ParkingLot::findOrFail($validated['parking_lot_id']);
        $bookings = [];
    
        foreach ($validated['slot_ids'] as $slotId) {
            $slot = Slot::where('_id', $slotId)->where('parking_lot_id', $parkingLot->_id)->firstOrFail();
    
            // Check if already booked
            $exists = Booking::where('slot_id', $slot->_id)
                ->whereIn('status', ['confirmed', 'pending', 'active'])
                ->where(function ($query) use ($start, $end) {
                    $query->where('booking_start_datetime', '<', $end)
                          ->where('booking_end_datetime', '>', $start);
                })
                ->exists();
    
            if ($exists) {
                return response()->json(['message' => "Slot {$slot->slot_number} already booked for this time."], 409);
            }
    
            $hourlyRate = match ($slot->vehicle_type) {
                'car' => $parkingLot->car_price,
                'bike' => $parkingLot->bike_price,
                'bus' => $parkingLot->bus_price,
                default => 0,
            };
    
            $price = ($duration / 60) * $hourlyRate;

            $now = \Carbon\Carbon::now('Asia/Kolkata');
            $status = 'upcoming';
            if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                $status = 'active';
            }

            $booking = Booking::create([
                'user_id' => Auth::id(), // Booked by the owner
                'parking_lot_id' => $parkingLot->_id,
                'slot_id' => $slot->_id,
                'time_slot_id' => $start->format('H:i') . '-' . $end->format('H:i'),
                'date' => $start->format('Y-m-d'),
                'booking_start_datetime' => $start->toDateTimeString(),
                'booking_end_datetime' => $end->toDateTimeString(),
                'booking_duration_minutes' => $duration,
                'price' => $price,
                'status' => $status,
                'booking_id' => strtoupper(\Illuminate\Support\Str::random(10)),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'is_manual' => true,
                'payment_method' => 'cash' // Record that it was a manual cash booking
            ]);

            // Generate Invoice
            \App\Http\Controllers\InvoiceController::generateInvoice($booking);

            // Record transaction for owner dashboard
            Transaction::create([
                'user_id' => Auth::id(), // The owner who created it
                'owner_id' => Auth::id(), // The owner themselves
                'booking_id' => $booking->_id,
                'amount' => $price,
                'type' => 'earning',
                'status' => 'completed',
                'payment_method' => 'cash',
                'description' => "Manual Booking (Cash): Slot {$slot->slot_number} - {$validated['customer_name']}",
                'metadata' => [
                    'date' => $start->format('Y-m-d'),
                    'time_slot' => $start->format('H:i') . '-' . $end->format('H:i'),
                    'customer' => $validated['customer_name']
                ]
            ]);
            
            $bookings[] = $booking;
        }
    
        return response()->json([
            'message' => 'Manual bookings successful',
            'bookings' => $bookings
        ], 201);
    }

    private function generateVehicleSlots(ParkingLot $parkingLot, $carSlots, $bikeSlots, $busSlots)
    {
        $slots = [];
        
        for ($i = 1; $i <= $carSlots; $i++) {
            $slots[] = [
                'parking_lot_id' => $parkingLot->_id,
                'slot_number' => 'C' . $i,
                'vehicle_type' => 'car',
                'slot_type' => 'standard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        for ($i = 1; $i <= $bikeSlots; $i++) {
            $slots[] = [
                'parking_lot_id' => $parkingLot->_id,
                'slot_number' => 'B' . $i,
                'vehicle_type' => 'bike',
                'slot_type' => 'standard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        for ($i = 1; $i <= $busSlots; $i++) {
            $slots[] = [
                'parking_lot_id' => $parkingLot->_id,
                'slot_number' => 'BS' . $i,
                'vehicle_type' => 'bus',
                'slot_type' => 'standard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($slots)) {
            Slot::insert($slots);
        }
    }

    public function getBookings(Request $request)
    {
        $owner = Auth::user();
        $lots = ParkingLot::where('owner_id', $owner->_id)->get();
        $lotIds = $lots->pluck('_id');

        $query = Booking::whereIn('parking_lot_id', $lotIds)
            ->with(['parkingLot', 'slot', 'user']);

        // Filter by status if specified
        if ($request->has('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'active_now') {
                $now = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d');
                $query->where('date', $now)
                    ->where('status', 'active');
            } elseif ($status === 'today') {
                $now = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d');
                $query->where('date', $now);
            } else {
                $query->where('status', $status);
            }
        }

        // Search text matching booking_id, vehicle_number, customer_name, or customer_phone
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = strtoupper($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('booking_id', 'like', '%' . $search . '%')
                  ->orWhere('vehicle_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%');
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($bookings);
    }

    public function verifyBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|string|size:10',
        ]);

        $booking = Booking::where('booking_id', strtoupper($validated['booking_id']))->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking ID not found.'
            ], 404);
        }

        // Security check: must belong to a parking lot owned by the authenticated host
        $lot = ParkingLot::where('_id', $booking->parking_lot_id)->where('owner_id', Auth::id())->first();
        if (!$lot) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. This booking is not for your parking property.'
            ], 403);
        }

        // Check if payment is confirmed
        if ($booking->payment_status !== 'paid' && $booking->payment_method !== 'cash') {
            return response()->json([
                'success' => false,
                'message' => 'Payment for this booking is pending or unconfirmed.'
            ], 422);
        }

        // Verify status
        if (in_array($booking->status, ['completed', 'expired', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid booking status. Booking is already {$booking->status}."
            ], 422);
        }

        if ($booking->status === 'attended') {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle is already checked in (attended).'
            ], 422);
        }

        // Timing validation
        $start = $booking->getStartCarbon();
        $end = $booking->getEndCarbon();
        $now = \Carbon\Carbon::now('Asia/Kolkata');

        // Let's allow a grace period (e.g. 15 minutes early arrival or timing check)
        // A booking is valid if we are on the booking date and now is before the end time!
        if ($now->greaterThan($end)) {
            return response()->json([
                'success' => false,
                'message' => 'Booking time has expired.'
            ], 422);
        }

        if ($now->lessThan($start->subMinutes(15))) {
            return response()->json([
                'success' => false,
                'message' => 'Too early. Verification is permitted only within 15 minutes of the slot start time.'
            ], 422);
        }

        $slot = Slot::find($booking->slot_id);

        return response()->json([
            'success' => true,
            'message' => 'Valid Parking',
            'booking' => [
                'id' => $booking->_id,
                'booking_id' => $booking->booking_id,
                'customer_name' => $booking->customer_name ?? 'Guest',
                'customer_phone' => $booking->customer_phone ?? 'N/A',
                'vehicle_number' => $booking->vehicle_number ?? 'N/A',
                'vehicle_type' => $booking->vehicle_type ?? 'N/A',
                'slot_number' => $slot ? $slot->slot_number : 'N/A',
                'date' => $booking->date,
                'time_slot' => $booking->time_slot_id,
                'status' => $booking->status,
                'price' => $booking->price,
            ]
        ]);
    }

    public function markAttended(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|string',
        ]);

        $booking = Booking::where('_id', $validated['booking_id'])->first();
        if (!$booking) {
            $booking = Booking::where('booking_id', strtoupper($validated['booking_id']))->first();
        }

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        $lot = ParkingLot::where('_id', $booking->parking_lot_id)->where('owner_id', Auth::id())->first();
        if (!$lot) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        if ($booking->status === 'attended') {
            return response()->json([
                'success' => false,
                'message' => 'Already marked as attended.'
            ], 422);
        }

        $booking->update([
            'status' => 'attended',
            'attended_at' => \Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString(),
            'verified_by' => Auth::id(),
        ]);

        // Send confirmation notification
        try {
            if ($booking->user) {
                $booking->user->notify(new \App\Notifications\ParkingAttendanceConfirmed($booking));
            } else if ($booking->booking_email) {
                \Illuminate\Support\Facades\Notification::route('mail', $booking->booking_email)
                    ->notify(new \App\Notifications\ParkingAttendanceConfirmed($booking));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send check-in notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful! Attendance marked.',
            'booking' => $booking
        ]);
    }

    public function getClosureSummary($id)
    {
        $parkingLot = ParkingLot::where('_id', $id)->where('owner_id', Auth::id())->firstOrFail();

        $activeBookings = Booking::where('parking_lot_id', $parkingLot->_id)
            ->whereIn('status', ['confirmed', 'pending', 'upcoming', 'active'])
            ->get();
        
        $latestBooking = $activeBookings->sortByDesc('date')->first();
        
        $minDate = \Carbon\Carbon::today('Asia/Kolkata')->addDays(7);
        $closureDate = clone $minDate;

        if ($latestBooking) {
            $latestBookingDate = \Carbon\Carbon::parse($latestBooking->date, 'Asia/Kolkata');
            if ($latestBookingDate->greaterThanOrEqualTo($minDate)) {
                $closureDate = $latestBookingDate->addDay();
            }
        }

        $revenueImpact = $activeBookings->sum('price');

        return response()->json([
            'scheduled_removal_date' => $closureDate->format('Y-m-d'),
            'active_bookings_count' => $activeBookings->count(),
            'latest_booking_date' => $latestBooking ? $latestBooking->date : null,
            'revenue_impact' => $revenueImpact,
        ]);
    }

    public function scheduleClosure(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $parkingLot = ParkingLot::where('_id', $id)->where('owner_id', Auth::id())->firstOrFail();

        if (in_array($parkingLot->status, ['scheduled_for_removal', 'inactive', 'permanently_removed'])) {
            return response()->json(['message' => 'Parking lot is already scheduled for closure or inactive.'], 400);
        }

        $summaryRes = $this->getClosureSummary($id)->getData();

        $parkingLot->status = 'scheduled_for_removal';
        $parkingLot->removal_requested_at = \Carbon\Carbon::now('Asia/Kolkata')->toDateTimeString();
        $parkingLot->scheduled_removal_date = $summaryRes->scheduled_removal_date;
        $parkingLot->removal_reason = $validated['reason'] ?? null;
        $parkingLot->removed_by = Auth::id();
        $parkingLot->save();

        return response()->json([
            'message' => 'Closure scheduled successfully.',
            'scheduled_removal_date' => $parkingLot->scheduled_removal_date,
            'active_bookings_count' => $summaryRes->active_bookings_count
        ]);
    }

    public function toggleAcceptingBookings(Request $request, $id)
    {
        $parkingLot = ParkingLot::where('_id', $id)->where('owner_id', Auth::id())->firstOrFail();

        if (in_array($parkingLot->status, ['inactive', 'permanently_removed'])) {
            return response()->json(['message' => 'Cannot toggle bookings for inactive parking lots.'], 400);
        }

        $isAccepting = filter_var($request->input('is_accepting_bookings', true), FILTER_VALIDATE_BOOLEAN);
        $parkingLot->is_accepting_bookings = $isAccepting;
        $parkingLot->save();

        return response()->json([
            'message' => 'Booking status updated successfully.',
            'is_accepting_bookings' => $parkingLot->is_accepting_bookings
        ]);
    }

    public function cancelClosure(Request $request, $id)
    {
        $parkingLot = ParkingLot::where('_id', $id)->where('owner_id', Auth::id())->firstOrFail();

        if (!in_array($parkingLot->status, ['scheduled_for_removal', 'closing_soon'])) {
            return response()->json(['message' => 'This parking lot does not have a scheduled closure to cancel.'], 400);
        }

        if (in_array($parkingLot->status, ['inactive', 'permanently_removed'])) {
            return response()->json(['message' => 'Cannot cancel closure for an already deactivated parking lot.'], 400);
        }

        $parkingLot->status = 'active';
        $parkingLot->scheduled_removal_date = null;
        $parkingLot->removal_requested_at = null;
        $parkingLot->removal_reason = null;
        $parkingLot->removed_by = null;
        $parkingLot->is_accepting_bookings = true;
        $parkingLot->save();

        return response()->json([
            'message' => 'Scheduled closure has been cancelled. Parking lot is now active.',
            'status' => $parkingLot->status
        ]);
    }
}
