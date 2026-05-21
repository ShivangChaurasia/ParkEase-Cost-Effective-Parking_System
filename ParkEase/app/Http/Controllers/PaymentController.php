<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\ParkingLot;

class PaymentController extends Controller
{
    /**
     * Create a new Razorpay Order dynamically based on the total booking amount.
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string',
            'parking_lot_id' => 'required|string',
            'date' => 'required|date',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'required|string',
        ]);

        // Lifecycle Revalidation Guard
        $parkingLot = ParkingLot::find($request->parking_lot_id);
        if (!$parkingLot) {
            return response()->json(['success' => false, 'message' => 'Parking lot not found.'], 404);
        }

        if (in_array($parkingLot->status, ['inactive', 'permanently_removed'])) {
            return response()->json(['success' => false, 'message' => 'This parking area is no longer operational.'], 403);
        }

        if ($parkingLot->is_accepting_bookings === false) {
            return response()->json(['success' => false, 'message' => 'This parking area is currently not accepting bookings.'], 403);
        }

        if ($parkingLot->scheduled_removal_date && $request->date > $parkingLot->scheduled_removal_date) {
            return response()->json(['success' => false, 'message' => 'This parking area will not be operational on your selected booking date.'], 403);
        }

        // Validate booking date is within 7-day window
        $maxDate = \Carbon\Carbon::now('Asia/Kolkata')->addDays(7)->format('Y-m-d');
        if ($request->date > $maxDate) {
            return response()->json(['success' => false, 'message' => 'Bookings can only be made up to 7 days in advance.'], 422);
        }

        $start = \Carbon\Carbon::parse($request->start_datetime, 'Asia/Kolkata');
        $end = \Carbon\Carbon::parse($request->end_datetime, 'Asia/Kolkata');
        $duration = $start->diffInMinutes($end);

        if ($start->isPast()) {
            return response()->json(['success' => false, 'message' => 'This slot time has already passed.'], 422);
        }

        $totalCalculatedPrice = 0;
        foreach ($request->slot_ids as $slotId) {
            $slot = \App\Models\Slot::where('_id', $slotId)->where('parking_lot_id', $parkingLot->_id)->first();
            if (!$slot) continue;

            $existingBooking = \App\Models\Booking::where('parking_lot_id', $parkingLot->_id)
                ->where('slot_id', $slot->_id)
                ->whereIn('status', ['confirmed', 'pending', 'active'])
                ->where(function ($query) use ($start, $end) {
                    $query->where('booking_start_datetime', '<', $end)
                          ->where('booking_end_datetime', '>', $start);
                })->exists();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => "Slot {$slot->slot_number} is already booked for this duration.",
                ], 409);
            }

            $hourlyRate = match ($slot->vehicle_type) {
                'car' => $parkingLot->car_price,
                'bike' => $parkingLot->bike_price,
                'bus' => $parkingLot->bus_price,
                default => 0,
            };
            $totalCalculatedPrice += ($duration / 60) * $hourlyRate;
        }

        // Trust backend price if calculated, otherwise fallback to request amount
        $finalAmount = $totalCalculatedPrice > 0 ? $totalCalculatedPrice : $request->amount;

        try {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            // Razorpay accepts amount in paisa (for INR), so we multiply by 100
            $amountInPaise = (int) round($finalAmount * 100);
            $orderData = [
                'receipt'         => 'rcptid_' . time(),
                'amount'          => $amountInPaise, 
                'currency'        => $request->currency ?? 'INR',
                'payment_capture' => 1 // auto capture
            ];

            Log::info('Razorpay Order Creation Payload: ', $orderData);

            $razorpayOrder = $api->order->create($orderData);

            Log::info('Razorpay Order Created: ', $razorpayOrder->toArray());

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'key' => env('RAZORPAY_KEY')
            ]);
            
        } catch (Exception $e) {
            Log::error('Razorpay Order Creation Error: ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate order ID: ' . $e->getMessage()
            ], 500);
        }
    }
}
