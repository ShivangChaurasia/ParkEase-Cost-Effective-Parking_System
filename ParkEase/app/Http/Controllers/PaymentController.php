<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

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
        ]);

        try {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

            // Razorpay accepts amount in paisa (for INR), so we multiply by 100
            $amountInPaise = (int) round($request->amount * 100);
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
