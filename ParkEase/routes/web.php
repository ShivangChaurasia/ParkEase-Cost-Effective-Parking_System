<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', function () {
    return view('search');
});

Route::get('/parking/{id}', function ($id) {
    // We will just pass ID to view, and let JS fetch the details
    return view('parking', ['id' => $id]);
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::post('/api/register', [AuthController::class, 'register']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/api/search', [SearchController::class, 'search']);

Route::middleware('auth')->group(function () {
    Route::post('/api/owner/parking-lots', [OwnerController::class, 'storeParkingLot']);
    
    Route::get('/api/parking-lots/{parkingLotId}/slots', [BookingController::class, 'getSlots']);
    Route::post('/api/bookings', [BookingController::class, 'createBooking']);
});
