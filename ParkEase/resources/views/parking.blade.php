@extends('layouts.app')

@section('title', 'Book Parking Slot')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Parking Info & Selection Form -->
            <div class="card p-4 shadow h-100" style="background: var(--glass-bg);">
                <h3 class="fw-bold text-dark mb-1" id="parkingName">Loading...</h3>
                <p class="text-muted small mb-4" id="parkingAddress">Loading address...</p>
                
                <h5 class="fw-bold text-dark mb-3">1. Select Date & Time</h5>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Date</label>
                    <input type="date" id="bookingDate" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted">Time Slot</label>
                    <select id="timeSlot" class="form-select">
                        <option value="10:00-11:00">10:00 AM - 11:00 AM</option>
                        <option value="11:00-12:00">11:00 AM - 12:00 PM</option>
                        <option value="12:00-13:00">12:00 PM - 01:00 PM</option>
                        <option value="13:00-14:00">01:00 PM - 02:00 PM</option>
                        <option value="14:00-15:00">02:00 PM - 03:00 PM</option>
                    </select>
                </div>

                <button id="checkAvailabilityBtn" class="btn btn-outline-dark w-100 mb-4">Check Availability</button>

                <div id="bookingSummary" class="d-none mt-auto">
                    <hr class="border-secondary">
                    <h5 class="fw-bold text-dark">Booking Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Selected Slot:</span>
                        <span class="fw-bold text-dark" id="summarySlot">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Price:</span>
                        <span class="fw-bold" style="color: var(--primary);" id="summaryPrice">₹0</span>
                    </div>
                    <button id="confirmBookingBtn" class="btn btn-primary-custom w-100 py-3 fs-5" disabled>Confirm Booking</button>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Movie Theater Grid -->
            <div class="card p-5 shadow text-center h-100" style="background: var(--card-bg);">
                <h4 class="fw-bold mb-4">2. Select Your Slot</h4>
                
                <div class="d-flex justify-content-center mb-4 gap-4">
                    <div class="d-flex align-items-center"><div class="slot-box slot-available mx-2" style="width:20px;height:20px;pointer-events:none;"></div> <span class="small text-muted">Available</span></div>
                    <div class="d-flex align-items-center"><div class="slot-box slot-selected mx-2" style="width:20px;height:20px;pointer-events:none;"></div> <span class="small text-muted">Selected</span></div>
                    <div class="d-flex align-items-center"><div class="slot-box slot-booked mx-2" style="width:20px;height:20px;pointer-events:none;"></div> <span class="small text-muted">Booked</span></div>
                </div>

                <div id="gridLoading" class="d-none my-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <div id="slotGridContainer" class="d-inline-block text-center mt-3" style="max-width: 100%; overflow-x: auto;">
                    <p class="text-muted my-5">Please select Date & Time and click "Check Availability" to view slots.</p>
                </div>
                
                <!-- Screen/Entrance indicator -->
                <div class="mt-5 pt-4 border-top border-secondary position-relative">
                    <span class="bg-light px-3 position-absolute top-0 start-50 translate-middle text-muted small fw-bold border" style="letter-spacing: 2px;">ENTRANCE / EXIT</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const parkingId = '{{ $id }}';
    let selectedSlotId = null;
    let selectedSlotNumber = null;
    let pricePerSlot = 0;

    // We need to fetch parking details first to show name/price.
    // For demo, we are doing an extra call or hardcoding if we had an endpoint. 
    // Wait, we don't have a specific `getParkingLot` endpoint, but we can just use the search endpoint to find it.
    // Let's assume price is ₹50 for now or fetch it from slots endpoint later.
    pricePerSlot = 50; 
    document.getElementById('parkingName').innerText = "Premium Parking " + parkingId.substring(0,4);
    
    document.getElementById('checkAvailabilityBtn').addEventListener('click', function() {
        const date = document.getElementById('bookingDate').value;
        const timeSlotId = document.getElementById('timeSlot').value;
        
        if (!date) return alert("Please select a date");
        
        document.getElementById('gridLoading').classList.remove('d-none');
        document.getElementById('slotGridContainer').innerHTML = '';
        document.getElementById('bookingSummary').classList.remove('d-none');
        resetSelection();

        fetch(`/api/parking-lots/${parkingId}/slots?date=${date}&time_slot_id=${timeSlotId}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if(response.status === 401) {
                window.location.href = '/login';
                throw new Error("Unauthorized");
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('gridLoading').classList.add('d-none');
            const slots = data.slots;
            
            if(!slots || slots.length === 0) {
                document.getElementById('slotGridContainer').innerHTML = '<p class="text-danger">No slots configured for this parking lot.</p>';
                return;
            }

            // Organize slots into rows and cols
            let gridHtml = '<div class="d-flex flex-column align-items-center">';
            
            // Group by row
            const rows = {};
            slots.forEach(slot => {
                if(!rows[slot.row]) rows[slot.row] = [];
                rows[slot.row].push(slot);
            });

            // Sort rows and columns
            Object.keys(rows).sort((a,b) => a - b).forEach(r => {
                rows[r].sort((a,b) => a.column - b.column);
                
                gridHtml += '<div class="d-flex mb-2 justify-content-center">';
                
                // Add row label
                gridHtml += `<div class="d-flex align-items-center me-3 text-muted fw-bold" style="width: 20px;">${rows[r][0].slot_number.replace(/[0-9]/g, '')}</div>`;
                
                rows[r].forEach(slot => {
                    if (slot.is_booked) {
                        gridHtml += `<div class="slot-box slot-booked" title="Booked">${slot.slot_number}</div>`;
                    } else {
                        gridHtml += `<div class="slot-box slot-available selectable-slot" data-id="${slot._id}" data-number="${slot.slot_number}">${slot.slot_number}</div>`;
                    }
                });
                
                gridHtml += '</div>';
            });
            gridHtml += '</div>';
            
            document.getElementById('slotGridContainer').innerHTML = gridHtml;

            // Attach click events
            document.querySelectorAll('.selectable-slot').forEach(el => {
                el.addEventListener('click', function() {
                    document.querySelectorAll('.selectable-slot').forEach(s => s.classList.remove('slot-selected'));
                    this.classList.add('slot-selected');
                    selectedSlotId = this.dataset.id;
                    selectedSlotNumber = this.dataset.number;
                    
                    document.getElementById('summarySlot').innerText = selectedSlotNumber;
                    document.getElementById('summaryPrice').innerText = '₹' + pricePerSlot;
                    document.getElementById('confirmBookingBtn').disabled = false;
                });
            });
        })
        .catch(err => {
            console.error(err);
            document.getElementById('gridLoading').classList.add('d-none');
        });
    });

    function resetSelection() {
        selectedSlotId = null;
        selectedSlotNumber = null;
        document.getElementById('summarySlot').innerText = '-';
        document.getElementById('summaryPrice').innerText = '₹0';
        document.getElementById('confirmBookingBtn').disabled = true;
    }

    document.getElementById('confirmBookingBtn').addEventListener('click', function() {
        const date = document.getElementById('bookingDate').value;
        const timeSlotId = document.getElementById('timeSlot').value;
        
        const btn = this;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        btn.disabled = true;

        fetch('/api/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                parking_lot_id: parkingId,
                slot_id: selectedSlotId,
                time_slot_id: timeSlotId,
                date: date
            })
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(res => {
            if (res.status === 201) {
                alert("Booking Confirmed! Booking ID: " + res.body.booking.booking_id);
                window.location.href = '/dashboard';
            } else {
                alert("Error: " + (res.body.message || "Could not complete booking."));
                btn.innerHTML = 'Confirm Booking';
                btn.disabled = false;
                // Refresh grid
                document.getElementById('checkAvailabilityBtn').click();
            }
        })
        .catch(err => {
            console.error(err);
            alert("Network error occurred.");
            btn.innerHTML = 'Confirm Booking';
            btn.disabled = false;
        });
    });
</script>
@endpush
