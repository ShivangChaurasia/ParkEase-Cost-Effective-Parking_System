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
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" id="cust_phone" class="form-control rounded-3" placeholder="10-digit mobile number" required>
                            <div class="form-text small">Required for seamless payment experience.</div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3">Select Payment Method</h5>
                        <div class="d-grid gap-2">
                            <!-- Razorpay Options -->
                            <div class="form-check card p-3 border rounded-3 cursor-pointer payment-option active" data-method="razorpay">
                                <input class="form-check-input d-none" type="radio" name="paymentMethod" id="razorpay_gateway" value="razorpay" checked>
                                <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="razorpay_gateway">
                                    <i class="bi bi-credit-card fs-4 me-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold">Pay Online (Instant)</div>
                                        <div class="small text-muted text-nowrap">UPI, Cards, Net Banking</div>
                                    </div>
                                    <i class="bi bi-check-circle-fill ms-auto text-primary check-icon"></i>
                                </label>
                            </div>
                            
                            <!-- Manual QR Option -->
                            <div class="form-check card p-3 border rounded-3 cursor-pointer payment-option" data-method="manual_qr">
                                <input class="form-check-input d-none" type="radio" name="paymentMethod" id="manual_qr" value="manual_qr">
                                <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="manual_qr">
                                    <i class="bi bi-qr-code-scan fs-4 me-3 text-success"></i>
                                    <div>
                                        <div class="fw-bold">Scan & Pay via UPI</div>
                                        <div class="small text-muted text-nowrap">PhonePe, GPay, Paytm (Manual)</div>
                                    </div>
                                    <i class="bi bi-circle ms-auto text-muted check-icon"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Manual QR Payment Section (Hidden by default) -->
                    <div id="qrPaymentSection" class="mb-4 d-none">
                        <div class="card bg-light border border-success-subtle rounded-4 p-4 text-center position-relative overflow-hidden shadow-sm" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);">
                            <h5 class="fw-bold text-success mb-2">Manual UPI Payment</h5>
                            <p class="small text-muted mb-3">Scan this QR code using any UPI app to pay</p>
                            
                            <div class="bg-white p-2 d-inline-block rounded-4 shadow-sm mb-3">
                                <img src="/images/phonepe-qr.png" alt="PhonePe QR Code" class="img-fluid rounded-3" style="width: 200px; height: 200px; object-fit: contain;">
                            </div>
                            
                            <div class="d-flex justify-content-center gap-3 mb-3 text-muted">
                                <span><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Google_Pay_Logo_%282020%29.svg/1200px-Google_Pay_Logo_%282020%29.svg.png" style="height:20px;" alt="GPay"></span>
                                <span><img src="https://download.logo.wine/logo/PhonePe/PhonePe-Logo.wine.png" style="height:20px; object-fit: cover" alt="PhonePe"></span>
                                <span><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Paytm_Logo_%28standalone%29.svg/1200px-Paytm_Logo_%28standalone%29.svg.png" style="height:20px;" alt="Paytm"></span>
                            </div>

                            <div class="bg-success text-white py-2 px-3 rounded-pill d-inline-block fw-bold mb-4 shadow-sm">
                                Amount to Pay: <span id="qrAmountDisplay">₹0</span>
                            </div>
                            
                            <div class="alert alert-warning small mb-4 py-2 border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                                <i class="bi bi-info-circle-fill me-1"></i> Please do not close this window until you have completed the payment on your app.
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

<!-- Confirm QR Payment Modal -->
<div class="modal fade" id="confirmQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bi bi-question-circle text-warning mb-3" style="font-size: 3rem;"></i>
                <h4 class="fw-bold mb-3">Confirm Payment</h4>
                <p class="text-muted mb-4">Are you sure you have successfully completed the payment of <strong id="modalQrAmount" class="text-dark"></strong> via your UPI app?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light border px-4 py-2" data-bs-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-success px-4 py-2" id="confirmManualBtn">Yes, I Have Paid</button>
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
            <h3 class="fw-bold text-success mb-2">Booking Reserved!</h3>
            <p class="text-muted mb-4" id="successModalMsg">Your parking slots have been booked. Check your dashboard for the ticket.</p>
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
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
        document.getElementById('qrAmountDisplay').innerText = '₹' + total;
        document.getElementById('modalQrAmount').innerText = '₹' + total;

        let selectedMethod = 'razorpay';
        const payNowBtn = document.getElementById('payNowBtn');

        // Payment Option Toggles
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(o => {
                    o.classList.remove('active');
                    o.querySelector('.check-icon').classList.replace('bi-check-circle-fill', 'bi-circle');
                    o.querySelector('.check-icon').classList.replace('text-primary', 'text-muted');
                    o.querySelector('.check-icon').classList.replace('text-success', 'text-muted');
                });
                this.classList.add('active');
                selectedMethod = this.getAttribute('data-method');
                this.querySelector('input').checked = true;
                
                const iconColor = selectedMethod === 'razorpay' ? 'text-primary' : 'text-success';
                this.querySelector('.check-icon').classList.replace('bi-circle', 'bi-check-circle-fill');
                this.querySelector('.check-icon').classList.replace('text-muted', iconColor);
                
                // Toggle QR Section
                if (selectedMethod === 'manual_qr') {
                    document.getElementById('qrPaymentSection').classList.remove('d-none');
                    payNowBtn.innerHTML = 'I Have Completed Payment';
                    payNowBtn.classList.replace('btn-primary-custom', 'btn-success');
                } else {
                    document.getElementById('qrPaymentSection').classList.add('d-none');
                    payNowBtn.innerHTML = 'Pay & Confirm Booking';
                    payNowBtn.classList.replace('btn-success', 'btn-primary-custom');
                }
            });
        });

        // Setup Manual QR Confirmation Modal
        const confirmQrModal = new bootstrap.Modal(document.getElementById('confirmQrModal'));
        document.getElementById('confirmManualBtn').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
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
                        customer_name: document.getElementById('cust_name').value,
                        customer_phone: document.getElementById('cust_phone').value,
                        payment_method: 'manual_qr'
                    })
                });

                if (response.ok) {
                    confirmQrModal.hide();
                    sessionStorage.removeItem('pending_booking');
                    document.getElementById('successModalMsg').innerText = "Your payment is pending verification. Check dashboard for status.";
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    const err = await response.json();
                    alert('Error: ' + (err.message || 'Verification failed'));
                    btn.disabled = false;
                    btn.innerHTML = 'Yes, I Have Paid';
                }
            } catch (err) {
                console.error(err);
                alert('Request failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = 'Yes, I Have Paid';
            }
        });

        // Final Payment Action with Razorpay Integration
        payNowBtn.addEventListener('click', async function() {
            if (selectedMethod === 'manual_qr') {
                confirmQrModal.show();
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Initializing Payment...';

            try {
                // Step 1: Create an order on the server
                const orderResponse = await fetch('/api/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ amount: total })
                });

                const orderDataResp = await orderResponse.json();

                if (!orderResponse.ok || !orderDataResp.success) {
                    throw new Error(orderDataResp.message || 'Failed to create order');
                }

                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Opening Secure Gateway...';

                // Step 2: Initialize Premium Razorpay Checkout
                const options = {
                    "key": orderDataResp.key, // The Key ID generated from the Dashboard
                    "amount": orderDataResp.amount, // Amount is in currency subunits. Default currency is INR.
                    "currency": orderDataResp.currency,
                    "name": "ParkEase Premium",
                    "description": "Secure Parking Reservation",
                    "image": "/favicon.ico", // You can replace with your logo URL
                    "order_id": orderDataResp.order_id, // This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                    
                    // Premium UI Configuration: UPI First
                    "config": {
                        "display": {
                            "blocks": {
                                "upi": {
                                    "name": "Recommended: Pay via UPI",
                                    "instruments": [
                                        { "method": "upi" },
                                        { "method": "upi", "provider": "google_pay" },
                                        { "method": "upi", "provider": "phonepe" },
                                        { "method": "upi", "provider": "paytm" }
                                    ]
                                },
                                "other": {
                                    "name": "Other Payment Modes",
                                    "instruments": [
                                        { "method": "card" },
                                        { "method": "netbanking" },
                                        { "method": "wallet" }
                                    ]
                                }
                            },
                            "hide": [
                                { "method": "emi" },
                                { "method": "paylater" }
                            ],
                            "sequence": ["block.upi", "block.other"],
                            "preferences": {
                                "show_default_blocks": false
                            }
                        }
                    },
                    "retry": {
                        "enabled": false // Let us handle failure gracefully
                    },
                    "handler": async function (response){
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Confirming Booking...';
                        
                        // Step 3: Verify signature and create booking on server
                        try {
                            const verifyResponse = await fetch('/api/bookings', {
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
                                    customer_name: document.getElementById('cust_name').value,
                                    customer_phone: document.getElementById('cust_phone').value,
                                    payment_method: 'razorpay',
                                    // Razorpay details
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });

                            if (verifyResponse.ok) {
                                sessionStorage.removeItem('pending_booking');
                                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                                successModal.show();
                            } else {
                                const err = await verifyResponse.json();
                                alert('Error confirming booking: ' + (err.message || 'Verification failed'));
                                btn.disabled = false;
                                btn.innerHTML = 'Pay & Confirm Booking';
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Failed to communicate with server. Contact support if amount was deducted.');
                            btn.disabled = false;
                            btn.innerHTML = 'Pay & Confirm Booking';
                        }
                    },
                    "prefill": {
                        "name": document.getElementById('cust_name').value,
                        "email": document.getElementById('cust_email').value,
                        "contact": document.getElementById('cust_phone').value || ""
                    },
                    "theme": {
                        "color": "#000000" // Premium Dark Mode feel for gateway
                    },
                    "modal": {
                        "ondismiss": function(){
                            btn.disabled = false;
                            btn.innerHTML = 'Pay & Confirm Booking';
                        },
                        "animation": true,
                        "backdropclose": false
                    }
                };
                
                const rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', function (response){
                    alert("Payment Failed: " + response.error.description);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay & Confirm Booking';
                });
                rzp1.open();

            } catch (err) {
                console.error(err);
                alert(err.message || 'Payment initialization failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = 'Pay & Confirm Booking';
            }
        });
    });
</script>
@endpush
