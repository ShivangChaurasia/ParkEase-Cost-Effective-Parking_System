<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\ParkingLot;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        $bookings = Booking::where(function($query) use ($user) {
                $query->where('user_id', $user->_id)
                      ->orWhere('booking_email', $user->email);
            })
            ->with(['parkingLot:id,name,city,pincode,latitude,longitude', 'slot:id,slot_number,vehicle_type'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('dashboard', compact('bookings'));
    }

    public function ownerDashboard()
    {
        $owner = Auth::user();

        if ($owner->kyc_status !== 'verified') {
            return redirect('/owner/kyc');
        }
        
        $parkingLots = ParkingLot::where('owner_id', $owner->_id)->get();
        
        // Basic analytics
        $totalParkingLots = $parkingLots->count();
        $totalSlots = \App\Models\Slot::whereIn('parking_lot_id', $parkingLots->pluck('_id'))->count();
        $activeBookings = Booking::whereIn('parking_lot_id', $parkingLots->pluck('_id'))
            ->where('status', 'confirmed')
            ->count();
            
        return view('owner.dashboard', compact('parkingLots', 'totalParkingLots', 'totalSlots', 'activeBookings'));
    }
}
