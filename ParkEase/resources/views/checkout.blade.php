@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="bg-dark text-white p-4 text-center">
                    <h3 class="fw-bold mb-0">Order Summary</h3>
                    <p class="text-white-50 small mb-0">Confirm your details and pay</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Booking Details -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Parking Details</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Parking Area:</span>
                            <span class="fw-bold text-dark">{{ $lot->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Location:</span>
                            <span class="small text-end">{{ $lot->address }}, {{ $lot->city }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date & Time:</span>
                            <span class="fw-bold" id="displayDateTime">Loading...</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Slot Breakdown</h5>
                        <div id="slotList">
                            <!-- JS populated -->
                        </div>
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top border-2">
                            <span class="h5 fw-bold">Total Amount</span>
                            <span class="h5 fw-bold text-success" id="totalAmount">₹0</span>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Customer Information</h5>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" id="cust_name" class="form-control rounded-3" placeholder="Enter your name" value="{{ Auth::user()->name ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" id="cust_email" class="form-control rounded-3" placeholder="name@example.com" value="{{ Auth::user()->email ?? '' }}">
                            <div class="form-text small">Access this booking anytime using the same email.</div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3">Select Payment Method</h5>
                        <div class="d-grid gap-2">
                            <div class="form-check card p-3 border rounded-3 cursor-pointer payment-option active">
                                <input class="form-check-input d-none" type="radio" name="paymentMethod" id="upi" value="upi" checked>
                                <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="upi">
                                    <i class="bi bi-qr-code fs-4 me-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">UPI Payment</div>
                                        <div class="small text-muted text-nowrap">Pay using GPay, PhonePe, Paytm</div>
                                    </div>
                                    <i class="bi bi-check-circle-fill ms-auto text-primary check-icon"></i>
                                </label>
                            </div>
                            
                            <div class="form-check card p-3 border rounded-3 cursor-pointer payment-option">
                                <input class="form-check-input d-none" type="radio" name="paymentMethod" id="card" value="card">
                                <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="card">
                                    <i class="bi bi-credit-card fs-4 me-3 text-success"></i>
                                    <div>
                                        <div class="fw-bold">Credit / Debit Card</div>
                                        <div class="small text-muted text-nowrap">Visa, Mastercard, RuPay</div>
                                    </div>
                                    <i class="bi bi-circle ms-auto text-muted check-icon"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button id="payNowBtn" class="btn btn-primary-custom w-100 py-3 fs-5 shadow">
                        Pay & Confirm Booking
                    </button>
                    
                    <p class="text-center mt-3 small text-muted">
                        <i class="bi bi-shield-check text-success"></i> Secure 256-bit SSL Encrypted Payment
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-5 border-0 rounded-4">
            <div class="mb-4">
                <div class="success-checkmark mx-auto">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-2">Payment Successful!</h3>
            <p class="text-muted mb-4">Your parking slots have been booked. Check your dashboard for the ticket.</p>
            <a href="/dashboard" class="btn btn-primary-custom w-100 py-2">Go to Dashboard</a>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .payment-option { transition: all 0.2s; }
    .payment-option.active { border-color: var(--primary) !important; background-color: rgba(0,0,0,0.02); }
    .payment-option:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    
    /* Success Checkmark Animation */
    .success-checkmark {
        width: 80px;
        height: 115px;
        margin: 0 auto;
    }
    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #4CAF50;
    }
    .success-checkmark .check-icon::before {
        top: 3px; left: -2px; width: 30px; transform-origin: 100% 50%; border-radius: 100px 0 0 100px;
    }
    .success-checkmark .check-icon::after {
        top: 0; left: 30px; width: 60px; transform-origin: 0 50%; border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    .success-checkmark .icon-line {
        height: 5px; background-color: #4CAF50; display: block; border-radius: 2px; position: absolute; z-index: 10;
    }
    .success-checkmark .icon-line.line-tip {
        top: 46px; left: 14px; width: 25px; transform: rotate(45deg); animation: icon-line-tip 0.75s;
    }
    .success-checkmark .icon-line.line-long {
        top: 38px; right: 8px; width: 47px; transform: rotate(-45deg); animation: icon-line-long 0.75s;
    }
    .success-checkmark .icon-circle {
        top: -4px; left: -4px; z-index: 10; width: 80px; height: 80px; border-radius: 50%; border: 4px solid rgba(76, 175, 80, .5); position: absolute; box-sizing: content-box;
    }
    
    @keyframes icon-line-tip { 0% { width: 0; left: 1px; top: 19px; } 54% { width: 0; left: 1px; top: 19px; } 70% { width: 50px; left: -8px; top: 37px; } 84% { width: 17px; left: 21px; top: 48px; } 100% { width: 25px; left: 14px; top: 46px; } }
    @keyframes icon-line-long { 0% { width: 0; right: 46px; top: 54px; } 65% { width: 0; right: 46px; top: 54px; } 84% { width: 55px; right: 0px; top: 35px; } 100% { width: 47px; right: 8px; top: 38px; } }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load data from session storage (saved from parking page)
        const bookingData = JSON.parse(sessionStorage.getItem('pending_booking'));
        if (!bookingData || bookingData.lot_id !== '{{ $lot->_id }}') {
            window.location.href = '/parking/{{ $lot->_id }}';
            return;
        }

        const date = bookingData.date;
        const time = bookingData.time_slot_id;
        const slots = bookingData.slots; // Array of {id, number, type, price}
        
        let total = 0;

        document.getElementById('displayDateTime').innerText = `${date} | ${time}`;

        const slotList = document.getElementById('slotList');
        slots.forEach(slot => {
            total += slot.price;
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between mb-2 small';
            div.innerHTML = `<span class="text-muted">Slot ${slot.number} (${slot.type.toUpperCase()})</span> <span>₹${slot.price}</span>`;
            slotList.appendChild(div);
        });
        
        document.getElementById('totalAmount').innerText = '₹' + total;

        // Payment Option Toggles
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(o => {
                    o.classList.remove('active');
                    o.querySelector('.check-icon').classList.replace('bi-check-circle-fill', 'bi-circle');
                    o.querySelector('.check-icon').classList.replace('text-primary', 'text-muted');
                });
                this.classList.add('active');
                this.querySelector('input').checked = true;
                this.querySelector('.check-icon').classList.replace('bi-circle', 'bi-check-circle-fill');
                this.querySelector('.check-icon').classList.replace('text-muted', 'text-primary');
            });
        });

        // Final Payment Action
        document.getElementById('payNowBtn').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing Payment...';

            try {
                const response = await fetch('/api/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        parking_lot_id: bookingData.lot_id,
                        slot_ids: slots.map(s => s.id),
                        time_slot_id: bookingData.time_slot_id,
                        date: bookingData.date,
                        vehicle_type: bookingData.vehicle_type,
                        email: document.getElementById('cust_email').value,
                        customer_name: document.getElementById('cust_name').value
                    })
                });

                if (response.ok) {
                    sessionStorage.removeItem('pending_booking');
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    const err = await response.json();
                    alert('Error: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay & Confirm Booking';
                }
            } catch (err) {
                console.error(err);
                alert('Payment failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = 'Pay & Confirm Booking';
            }
        });
    });
</script>
@endpush
