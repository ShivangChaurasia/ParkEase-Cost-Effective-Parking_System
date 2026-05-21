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
use Razorpay\Api\Api;
use App\Models\Transaction;
use Exception;

class BookingController extends Controller
{
    public function getSlots(Request $request, $parkingLotId)
    {
        $validated = $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $requestedStart = \Carbon\Carbon::parse($validated['start_datetime'], 'Asia/Kolkata');
        $requestedEnd = \Carbon\Carbon::parse($validated['end_datetime'], 'Asia/Kolkata');

        if ($requestedStart->isPast()) {
            return response()->json(['slots' => []]);
        }

        $parkingLot = ParkingLot::find($parkingLotId);
        if (!$parkingLot || in_array($parkingLot->status, ['inactive', 'permanently_removed']) || $parkingLot->is_accepting_bookings === false) {
            return response()->json(['slots' => []]);
        }

        $date = $requestedStart->format('Y-m-d');
        if ($parkingLot->scheduled_removal_date && $date > $parkingLot->scheduled_removal_date) {
            return response()->json(['slots' => []]);
        }

        $slots = Slot::where('parking_lot_id', $parkingLotId)
            ->select(['_id', 'slot_number', 'vehicle_type', 'row', 'column'])
            ->get();
        
        $bookedSlotIds = Booking::where('parking_lot_id', $parkingLotId)
            ->whereIn('status', ['confirmed', 'pending', 'active'])
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->where('booking_start_datetime', '<', $requestedEnd)
                      ->where('booking_end_datetime', '>', $requestedStart);
            })->pluck('slot_id')->map(fn($id) => (string)$id)->toArray();

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
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'vehicle_type' => 'nullable|string',
            'email' => 'required|email',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'vehicle_number' => 'nullable|string|max:20',
            'payment_method' => 'required|string|in:razorpay,manual_qr',
            'razorpay_payment_id' => 'required_if:payment_method,razorpay|string|nullable',
            'razorpay_order_id' => 'required_if:payment_method,razorpay|string|nullable',
            'razorpay_signature' => 'required_if:payment_method,razorpay|string|nullable'
        ]);

        // Past-Time Slot Booking Validation (Authoritative Backend Guard)
        $start = \Carbon\Carbon::parse($validated['start_datetime'], 'Asia/Kolkata');
        $end = \Carbon\Carbon::parse($validated['end_datetime'], 'Asia/Kolkata');
        $duration = $start->diffInMinutes($end);

        if ($start->isPast()) {
            // Task 8: Warning logging in laravel.log
            \Illuminate\Support\Facades\Log::warning('Manual attempt to book a past time slot.', [
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

        // Verify Razorpay Signature only if method is razorpay
        if ($validated['payment_method'] === 'razorpay') {
            try {
                $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
                $attributes = array(
                    'razorpay_order_id' => $validated['razorpay_order_id'],
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'razorpay_signature' => $validated['razorpay_signature']
                );
                $api->utility->verifyPaymentSignature($attributes);
            } catch (Exception $e) {
                return response()->json(['message' => 'Payment verification failed: ' . $e->getMessage()], 400);
            }
        }

        $parkingLot = ParkingLot::findOrFail($validated['parking_lot_id']);
        
        // Final Dependency Revalidation
        if (in_array($parkingLot->status, ['inactive', 'permanently_removed']) || $parkingLot->is_accepting_bookings === false) {
            return response()->json(['message' => 'This parking area is currently not accepting bookings.'], 403);
        }

        $date = $start->format('Y-m-d');
        if ($parkingLot->scheduled_removal_date && $date > $parkingLot->scheduled_removal_date) {
            return response()->json(['message' => 'This parking area will not be operational on your selected booking date.'], 403);
        }

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
                ->whereIn('status', ['confirmed', 'pending', 'active'])
                ->where(function ($query) use ($start, $end) {
                    $query->where('booking_start_datetime', '<', $end)
                          ->where('booking_end_datetime', '>', $start);
                })->exists();

            if ($existingBooking) {
                return response()->json([
                    'message' => "Slot {$slot->slot_number} is already booked.",
                ], 409);
            }

            $hourlyRate = match ($slot->vehicle_type) {
                'car' => $parkingLot->car_price,
                'bike' => $parkingLot->bike_price,
                'bus' => $parkingLot->bus_price,
                default => 0,
            };
            $price = ($duration / 60) * $hourlyRate;

            $userId = Auth::id();
            $userEmail = $request->input('email');
            
            if (!$userId && $userEmail) {
                $existingUser = \App\Models\User::where('email', $userEmail)->first();
                if ($existingUser) {
                    $userId = $existingUser->_id;
                }
            }

            $now = \Carbon\Carbon::now('Asia/Kolkata');
            
            $status = 'upcoming';
            if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                $status = 'active';
            }

            $booking = Booking::create([
                'user_id' => $userId,
                'booking_email' => $userEmail,
                'parking_lot_id' => $parkingLot->_id,
                'slot_id' => $slot->_id,
                'time_slot_id' => $start->format('H:i') . '-' . $end->format('H:i'),
                'date' => $date,
                'booking_start_datetime' => $start->toDateTimeString(),
                'booking_end_datetime' => $end->toDateTimeString(),
                'booking_duration_minutes' => $duration,
                'price' => $price,
                'status' => $status,
                'payment_status' => $validated['payment_method'] === 'manual_qr' ? 'pending_verification' : 'paid',
                'booking_id' => strtoupper(Str::random(10)),
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'vehicle_type' => $slot->vehicle_type,
                'payment_method' => $validated['payment_method'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'] ?? null,
                'razorpay_order_id' => $validated['razorpay_order_id'] ?? null,
            ]);

            // Automatically generate the PDF invoice/ticket
            \App\Http\Controllers\InvoiceController::generateInvoice($booking);

            // Create Transaction Record
            Transaction::create([
                'user_id' => $userId,
                'owner_id' => $parkingLot->owner_id,
                'booking_id' => $booking->_id,
                'amount' => $price,
                'type' => 'earning',
                'status' => $validated['payment_method'] === 'manual_qr' ? 'pending' : 'completed',
                'payment_method' => $validated['payment_method'],
                'description' => "Booking for slot {$slot->slot_number} at {$parkingLot->name}",
                'metadata' => [
                    'date' => $validated['date'],
                    'time_slot' => $start->format('H:i') . '-' . $end->format('H:i')
                ]
            ]);

            $bookings[] = $booking;
        }

        return response()->json([
            'message' => 'Bookings confirmed successfully',
            'bookings' => $bookings
        ], 201);
    }

    public function cancelBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Security Check: Only the owner of the booking can cancel it
        $user = Auth::user();
        if ($booking->user_id !== $user->_id && $booking->booking_email !== $user->email) {
            return response()->json(['message' => 'Unauthorized. This is not your booking.'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled.'], 400);
        }

        // Calculate refund
        $bookingStart = $booking->booking_start_datetime 
            ? \Carbon\Carbon::parse($booking->booking_start_datetime)
            : \Carbon\Carbon::parse($booking->date . ' ' . explode('-', $booking->time_slot_id)[0]);
        $now = now();
        
        $hoursDiff = $now->diffInHours($bookingStart, false);
        $minsDiff = $now->diffInMinutes($bookingStart, false);

        $refundPercentage = 0;
        if ($minsDiff >= 120) {
            $refundPercentage = 100;
        } elseif ($minsDiff >= 30) {
            $refundPercentage = 50;
        } else {
            $refundPercentage = 0;
        }

        $refundAmount = ($booking->price * $refundPercentage) / 100;

        $booking->status = 'cancelled';
        $booking->refund_amount = $refundAmount;
        $booking->refund_status = $refundAmount > 0 ? 'processing' : 'none';
        $booking->cancelled_at = now();
        $booking->save();

        // Create Transaction Record for Refund
        if ($refundAmount > 0) {
            Transaction::create([
                'user_id' => $user->_id,
                'owner_id' => $booking->parkingLot->owner_id ?? null,
                'booking_id' => $booking->_id,
                'amount' => $refundAmount,
                'type' => 'refund',
                'status' => 'completed',
                'payment_method' => $booking->payment_method ?? 'original',
                'description' => "Refund for cancelled booking {$booking->booking_id}",
                'metadata' => [
                    'original_amount' => $booking->price,
                    'refund_percentage' => $refundPercentage
                ]
            ]);
        }

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'refund_amount' => $refundAmount,
            'refund_percentage' => $refundPercentage
        ]);
    }

    public function extendBooking(Request $request, $id)
    {
        $validated = $request->validate([
            'additional_duration_minutes' => 'required|integer|min:15|max:120',
        ]);

        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($booking->user_id !== $user->_id && $booking->booking_email !== $user->email) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!in_array($booking->status, ['confirmed', 'active'])) {
            return response()->json(['message' => 'Only active or confirmed bookings can be extended.'], 400);
        }

        // Parse current end time
        $currentEnd = $booking->booking_end_datetime 
            ? \Carbon\Carbon::parse($booking->booking_end_datetime)
            : \Carbon\Carbon::parse($booking->date . ' ' . explode('-', $booking->time_slot_id)[1]);

        $newEnd = $currentEnd->copy()->addMinutes($validated['additional_duration_minutes']);
        
        // Availability Check
        $collision = Booking::where('parking_lot_id', $booking->parking_lot_id)
            ->where('slot_id', $booking->slot_id)
            ->where('_id', '!=', $booking->_id)
            ->whereIn('status', ['confirmed', 'pending', 'active'])
            ->where(function ($query) use ($currentEnd, $newEnd) {
                $query->where('booking_start_datetime', '<', $newEnd)
                      ->where('booking_end_datetime', '>', $currentEnd);
            })->exists();

        if ($collision) {
            return response()->json(['message' => 'Cannot extend: The slot is reserved by another user during the extension period.'], 409);
        }

        // Calculate Cost
        $hourlyRate = match ($booking->vehicle_type) {
            'car' => $booking->parkingLot->car_price ?? 0,
            'bike' => $booking->parkingLot->bike_price ?? 0,
            'bus' => $booking->parkingLot->bus_price ?? 0,
            default => 0,
        };
        $extensionCost = ($validated['additional_duration_minutes'] / 60) * $hourlyRate;

        // Update Booking
        $booking->booking_end_datetime = $newEnd->toDateTimeString();
        $booking->booking_duration_minutes = ($booking->booking_duration_minutes ?? 0) + $validated['additional_duration_minutes'];
        $booking->time_slot_id = explode('-', $booking->time_slot_id)[0] . '-' . $newEnd->format('H:i');
        $booking->price += $extensionCost;
        $booking->save();

        // Create Transaction
        Transaction::create([
            'user_id' => $user->_id,
            'owner_id' => $booking->parkingLot->owner_id ?? null,
            'booking_id' => $booking->_id,
            'amount' => $extensionCost,
            'type' => 'payment',
            'status' => 'completed',
            'payment_method' => $booking->payment_method ?? 'original',
            'description' => "Extension ({$validated['additional_duration_minutes']} mins) for booking {$booking->booking_id}",
            'metadata' => [
                'additional_minutes' => $validated['additional_duration_minutes'],
                'new_end_time' => $newEnd->format('H:i')
            ]
        ]);

        return response()->json([
            'message' => 'Session extended successfully',
            'new_end_time' => $newEnd->format('H:i'),
            'cost' => $extensionCost
        ]);
    }
}
