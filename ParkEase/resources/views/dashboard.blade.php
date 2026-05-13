@extends('layouts.app')

@section('title', 'My Reservations & Activity')

@push('styles')
<style>
    :root {
        --active-color: #0d6efd;
        --upcoming-color: #198754;
        --completed-color: #6c757d;
        --cancelled-color: #dc3545;
        --glass-card: rgba(255, 255, 255, 0.85);
    }

    body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    .dashboard-header {
        padding: 40px 0;
        background: #000;
        color: white;
        border-radius: 0 0 40px 40px;
        margin-bottom: -40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .nav-tabs-custom {
        border: none;
        background: rgba(255, 255, 255, 0.5);
        padding: 8px;
        border-radius: 20px;
        display: inline-flex;
        backdrop-filter: blur(10px);
        flex-wrap: wrap;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 15px;
        padding: 10px 20px;
        font-weight: 700;
        color: #495057;
        transition: all 0.3s;
    }

    .nav-tabs-custom .nav-link.active {
        background: #000;
        color: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .search-bar {
        border-radius: 15px;
        border: 1px solid rgba(0,0,0,0.1);
        padding: 12px 20px;
        background: white;
        transition: all 0.3s;
    }

    /* Modal Styling */
    .modal-content-glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 32px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge-animated {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-active { background: rgba(13, 110, 253, 0.1); color: #0d6efd; border: 1px solid rgba(13, 110, 253, 0.2); }
    .status-upcoming { background: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2); }
    .status-completed { background: rgba(108, 117, 125, 0.1); color: #6c757d; border: 1px solid rgba(108, 117, 125, 0.2); }
    .status-cancelled { background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }

    .pulse-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { transform: scale(0.95); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.5; } 100% { transform: scale(0.95); opacity: 1; } }
</style>
@endpush

@section('content')
<div class="dashboard-header">
    <div class="container text-center">
        <h1 class="fw-bold display-5 mb-2">My Activity</h1>
        <p class="opacity-75">Manage your bookings and track your spending</p>
    </div>
</div>

<div class="container py-5">
    <!-- Search & Tabs -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-5 gap-4">
        <ul class="nav nav-tabs nav-tabs-custom" id="bookingTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#active-pane">Active</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#upcoming-pane">Upcoming</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#past-pane">Past</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelled-pane">Cancelled</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#transactions-pane"><i class="bi bi-credit-card me-1"></i> Transactions</button></li>
        </ul>

        <div class="d-flex gap-2 w-100" style="max-width: 400px;">
            <input type="text" class="form-control search-bar w-100" placeholder="Search bookings or activity..." id="bookingSearch">
        </div>
    </div>

    <div class="tab-content">
        <!-- Active Tab -->
        <div class="tab-pane fade show active" id="active-pane">
            @if(count($categorized['active']) > 0)
                <div class="row g-4">
                    @foreach($categorized['active'] as $booking)
                        @include('partials.booking_card', ['booking' => $booking, 'type' => 'active'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5"><h5 class="fw-bold">No Active Bookings</h5><p class="text-muted">You have no parking sessions running currently.</p></div>
            @endif
        </div>

        <!-- Upcoming Tab -->
        <div class="tab-pane fade" id="upcoming-pane">
            @if(count($categorized['upcoming']) > 0)
                <div class="row g-4">
                    @foreach($categorized['upcoming'] as $booking)
                        @include('partials.booking_card', ['booking' => $booking, 'type' => 'upcoming'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5"><h5 class="fw-bold">No Upcoming Bookings</h5><p class="text-muted">Plan your next trip and book a spot ahead of time.</p></div>
            @endif
        </div>

        <!-- Past Tab -->
        <div class="tab-pane fade" id="past-pane">
            @if(count($categorized['completed']) > 0)
                <div class="row g-4">
                    @foreach($categorized['completed'] as $booking)
                        @include('partials.booking_card', ['booking' => $booking, 'type' => 'completed'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5"><h5 class="fw-bold">No Past Bookings</h5></div>
            @endif
        </div>

        <!-- Cancelled Tab -->
        <div class="tab-pane fade" id="cancelled-pane">
            @if(count($categorized['cancelled']) > 0)
                <div class="row g-4">
                    @foreach($categorized['cancelled'] as $booking)
                        @include('partials.booking_card', ['booking' => $booking, 'type' => 'cancelled'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5"><h5 class="fw-bold">No Cancelled Bookings</h5></div>
            @endif
        </div>

        <!-- Transactions Tab -->
        <div class="tab-pane fade" id="transactions-pane">
            @if(count($transactions) > 0)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="py-3">Description</th>
                                    <th class="py-3">Method</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td class="px-4 py-3 text-muted small">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                        <td class="py-3">
                                            <div class="fw-bold">{{ $tx->description }}</div>
                                            <div class="text-muted small">ID: {{ substr($tx->_id, -8) }}</div>
                                        </td>
                                        <td class="py-3"><span class="badge bg-light text-dark border">{{ strtoupper($tx->payment_method) }}</span></td>
                                        <td class="py-3 fw-bold {{ $tx->type === 'refund' ? 'text-primary' : 'text-danger' }}">
                                            {{ $tx->type === 'refund' ? '+' : '-' }}₹{{ $tx->amount }}
                                        </td>
                                        <td class="py-3">
                                            <span class="badge {{ $tx->status === 'completed' ? 'bg-success' : ($tx->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ ucfirst($tx->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-5"><h5 class="fw-bold">No Transactions Found</h5></div>
            @endif
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-glass shadow-lg">
            <div class="modal-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3"><i class="bi bi-exclamation-triangle fs-2"></i></div>
                    <h3 class="fw-bold">Confirm Cancellation</h3>
                    <p class="text-muted">Are you sure you want to cancel this booking?</p>
                </div>
                <div class="card bg-light border-0 rounded-4 p-4 mb-4">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Parking:</span><span class="fw-bold" id="cancelModalParking"></span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Slot:</span><span class="fw-bold" id="cancelModalSlot"></span></div>
                    <hr class="my-3 opacity-10">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Paid Amount:</span><span class="fw-bold" id="cancelModalPaid"></span></div>
                    <div class="d-flex justify-content-between text-success fw-bold"><span>Refund Amount:</span><span id="cancelModalRefund"></span></div>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-light flex-grow-1 py-3 rounded-4 fw-bold" data-bs-dismiss="modal">Keep Booking</button>
                    <button class="btn btn-danger flex-grow-1 py-3 rounded-4 fw-bold shadow-sm" id="confirmCancelBtn">Confirm & Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-content-glass">
            <div class="modal-body text-center p-5">
                <h5 class="fw-bold mb-4">Entry QR Code</h5>
                <div class="bg-white p-3 rounded-4 shadow-sm mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16"><path d="M2 2h2v2H2V2Z"/><path d="M6 0v6H0V0h6ZM5 1H1v4h4V1ZM4 12H2v2h2v-2Z"/><path d="M6 10v6H0v-6h6Zm-5 1v4h4v-4H1Zm11-9h2v2h-2V2Z"/><path d="M10 0v6h6V0h-6Zm5 1v4h4V1h-4ZM8 1V0h1v2H8v2H7V1h1Zm0 5V4h1v2H8ZM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8H6Zm0 0v1H2V8H1v1H0V7h3v1h3Zm10 1h-1V7h1v2ZM10 10v6h6v-6h-6Zm1 1h4v4h-4v-4Zm-4-4v2h1v-1h1v1h1V7H7Zm1 10h-1v-1h2v-1h1v3h-1v-1h-1v1ZM8 10h1v1H8v-1Zm1 1v2H8v-1H7v1h1v-2h1Z"/></svg>
                </div>
                <button class="btn btn-dark w-100 mt-4 rounded-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeCancelId = null;
    function openCancelModal(id, parking, slot, paid, refund) {
        activeCancelId = id;
        document.getElementById('cancelModalParking').innerText = parking;
        document.getElementById('cancelModalSlot').innerText = slot;
        document.getElementById('cancelModalPaid').innerText = '₹' + paid;
        document.getElementById('cancelModalRefund').innerText = '₹' + refund;
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    document.getElementById('confirmCancelBtn').addEventListener('click', async function() {
        const btn = this; btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';
        try {
            const res = await fetch(`/api/bookings/${activeCancelId}/cancel`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }});
            if (res.ok) window.location.reload(); else alert('Failed.');
        } catch (err) { alert('Error.'); } finally { btn.disabled = false; }
    });

    document.getElementById('bookingSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.booking-card-wrapper, tr').forEach(el => {
            if (el.tagName === 'TR' && el.parentElement.tagName === 'THEAD') return;
            const text = el.innerText.toLowerCase();
            el.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
