@extends('layouts.app')
@section('title', 'User Dashboard')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">My Bookings</h2>
            <p class="text-muted">Manage and view your parking reservations.</p>
        </div>
    </div>

    @if($bookings->isEmpty())
        <div class="alert alert-light border text-center p-5 rounded-4 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="var(--primary)" class="bi bi-calendar-x mb-3" viewBox="0 0 16 16">
                <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708z"/>
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
            </svg>
            <h4 class="fw-bold text-dark">No active bookings found</h4>
            <p class="text-muted">You haven't booked any parking slots yet.</p>
            <a href="/" class="btn btn-primary-custom mt-2 px-4">Find Parking</a>
        </div>
    @else
        <div class="row">
            @foreach($bookings as $booking)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-lg booking-card-premium" style="border-radius: 20px; background: var(--glass-bg); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">{{ $booking->parkingLot->name ?? 'Unknown Parking' }}</h5>
                                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt-fill text-primary"></i> {{ $booking->parkingLot->city ?? '' }}, {{ $booking->parkingLot->pincode ?? '' }}</p>
                                </div>
                                    @php
                                        $isPast = $booking->date < date('Y-m-d');
                                        $isActive = $booking->date === date('Y-m-d');
                                    @endphp
                                    <span class="badge {{ $isPast ? 'bg-secondary' : ($isActive ? 'bg-primary shadow-sm' : 'bg-success') }} rounded-pill px-3 py-2">
                                        {{ $isPast ? 'Past' : ($isActive ? 'Active Today' : 'Upcoming') }}
                                    </span>
                                </div>
                            
                            <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-white shadow-sm">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <h4 class="mb-0 fw-bold">{{ $booking->slot->slot_number ?? '?' }}</h4>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.65rem;">Vehicle Type</div>
                                    <div class="fw-bold text-dark">{{ ucfirst($booking->vehicle_type ?? ($booking->slot->vehicle_type ?? 'standard')) }}</div>
                                </div>
                                <div class="ms-auto text-end">
                                    <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.65rem;">Amount</div>
                                    <div class="fw-bold text-success">₹{{ $booking->price }}</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="p-3 border rounded-4 text-center">
                                        <div class="small text-muted mb-1 ls-1" style="font-size: 0.65rem;">DATE</div>
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($booking->date)->format('D, M d') }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 border rounded-4 text-center">
                                        <div class="small text-muted mb-1 ls-1" style="font-size: 0.65rem;">TIME SLOT</div>
                                        <div class="fw-bold text-dark text-nowrap">{{ $booking->time_slot_id }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->parkingLot->latitude ?? '' }},{{ $booking->parkingLot->longitude ?? '' }}" target="_blank" class="btn btn-dark flex-grow-1 py-2 rounded-3">
                                    <i class="bi bi-navigation me-1"></i> Navigate
                                </a>
                                <button class="btn btn-outline-secondary rounded-3" title="View Ticket">
                                    <i class="bi bi-ticket-perforated"></i>
                                </button>
                            </div>
                            
                            <div class="mt-3 text-center d-flex justify-content-between align-items-center">
                                <span class="small text-muted ls-1" style="font-size: 0.6rem;">ID: {{ $booking->booking_id }}</span>
                                @if($isActive)
                                    <span class="small bg-primary-subtle text-primary fw-bold px-2 py-1 rounded" id="timer-{{ $booking->booking_id }}">
                                        <i class="bi bi-clock-history me-1"></i> Live
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Simple live timer simulation for active bookings
        const timers = document.querySelectorAll('[id^="timer-"]');
        setInterval(() => {
            timers.forEach(timer => {
                const now = new Date();
                timer.innerHTML = `<i class="bi bi-clock-history me-1"></i> ${now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})}`;
            });
        }, 1000);
    });
</script>

<style>
    .booking-card-premium {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .booking-card-premium:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    }
    .ls-1 { letter-spacing: 1px; }
</style>
@endsection
