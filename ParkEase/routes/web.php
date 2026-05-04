<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KycController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', function () {
    return view('search');
});

Route::get('/parking/{id}', function ($id) {
    $lot = \App\Models\ParkingLot::findOrFail($id);
    return view('parking', ['id' => $id, 'lot' => $lot]);
});

Route::get('/checkout', function (Illuminate\Http\Request $request) {
    $lot = \App\Models\ParkingLot::findOrFail($request->lot_id);
    return view('checkout', ['lot' => $lot]);
})->middleware(['auth', 'onboarded']);

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->middleware(['auth', 'onboarded']);

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/api/auth/clerk-sync', [AuthController::class, 'clerkSync']);

Route::get('/api/search', [SearchController::class, 'search']);
Route::get('/api/parking-lots/{parkingLotId}/slots', [BookingController::class, 'getSlots']);

Route::get('/switch-role', [AuthController::class, 'switchRole'])->middleware(['auth', 'onboarded']);

Route::get('/onboarding', [AuthController::class, 'showOnboarding'])->middleware('auth');
Route::post('/api/onboarding', [AuthController::class, 'submitOnboarding'])->middleware('auth');

Route::middleware(['auth', 'onboarded'])->group(function () {
    Route::get('/owner/kyc', [KycController::class, 'showKycForm']);
    Route::post('/api/owner/kyc', [KycController::class, 'submitKyc']);
    
    Route::get('/owner/dashboard', [DashboardController::class, 'ownerDashboard']);
    Route::get('/owner/parking/{id}/manage', [OwnerController::class, 'manageLot']);
    Route::post('/api/owner/parking-lots', [OwnerController::class, 'storeParkingLot']);
    Route::post('/api/owner/manual-booking', [OwnerController::class, 'storeManualBooking']);
    
    Route::post('/api/bookings', [BookingController::class, 'createBooking']);
});
