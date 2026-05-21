@extends('layouts.app')

@section('title', 'Manage Parking - ' . $parkingLot->name)

@section('content')
<div class="container py-5">
    @if(in_array($parkingLot->status, ['scheduled_for_removal', 'closing_soon']))
    <div class="alert {{ $parkingLot->status === 'closing_soon' ? 'alert-warning' : 'alert-danger' }} d-flex align-items-center rounded-4 mb-4 shadow-sm" role="alert">
        <i class="bi {{ $parkingLot->status === 'closing_soon' ? 'bi-exclamation-triangle-fill' : 'bi-calendar-x-fill' }} fs-4 me-3"></i>
        <div>
            <strong>{{ $parkingLot->status === 'closing_soon' ? 'Closing Soon' : 'Removal Scheduled' }}:</strong>
            This parking lot is scheduled for closure on <strong>{{ $parkingLot->scheduled_removal_date }}</strong>.
            No new bookings will be accepted after this date.
            @if($parkingLot->removal_reason)
                <br><small class="text-muted">Reason: {{ $parkingLot->removal_reason }}</small>
            @endif
        </div>
    </div>
    @elseif($parkingLot->status === 'inactive')
    <div class="alert alert-secondary d-flex align-items-center rounded-4 mb-4 shadow-sm" role="alert">
        <i class="bi bi-pause-circle-fill fs-4 me-3"></i>
        <div><strong>Inactive:</strong> This parking lot has been deactivated and is no longer operational.</div>
    </div>
    @endif
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1">{{ $parkingLot->name }}</h2>
            <p class="text-muted"><i class="bi bi-geo-alt"></i> {{ $parkingLot->address }}, {{ $parkingLot->city }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="dropdown d-inline-block me-2">
                <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i> Settings
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                        <label class="dropdown-item d-flex align-items-center justify-content-between" style="cursor: pointer;" id="toggleBookingsBtn">
                            <span id="toggleBookingsText">{{ $parkingLot->is_accepting_bookings ? 'Accepting Bookings' : 'Bookings Paused' }}</span>
                            <div class="form-check form-switch mb-0 ms-3">
                                <input class="form-check-input" type="checkbox" id="toggleBookingsSwitch" {{ $parkingLot->is_accepting_bookings ? 'checked' : '' }}>
                            </div>
                        </label>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="javascript:void(0)" onclick="openClosureModal()"><i class="bi bi-trash me-2"></i> Schedule Closure</a></li>
                    @if(in_array($parkingLot->status, ['scheduled_for_removal', 'closing_soon']))
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-success fw-bold" href="javascript:void(0)" onclick="cancelClosure()"><i class="bi bi-arrow-counterclockwise me-2"></i>Cancel Scheduled Closure</a></li>
                    @endif
                </ul>
            </div>
            <a href="/owner/dashboard" class="btn btn-dark btn-sm">Back to Dashboard</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card p-5 shadow-sm mb-4 border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Live Slot Status</h4>
                    <div class="d-flex gap-3">
                        <div class="mb-0">
                            <label class="small text-muted d-block">Select Date</label>
                            <input type="date" id="manageDate" class="form-control form-control-sm rounded-3" min="{{ date('Y-m-d') }}" max="{{ \Carbon\Carbon::now('Asia/Kolkata')->addDays(7)->format('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted d-block">Start Time</label>
                            <input type="time" id="manageTime" class="form-control form-control-sm rounded-3" value="10:00">
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted d-block">Duration</label>
                            <select id="manageDuration" class="form-select form-select-sm rounded-3">
                                <option value="30">30 mins</option>
                                <option value="60" selected>1 hour</option>
                                <option value="120">2 hours</option>
                                <option value="240">4 hours</option>
                                <option value="480">8 hours</option>
                                <option value="1440">24 hours</option>
                            </select>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-pills mb-4 nav-pills-custom" id="vehicleTabs">
                    <li class="nav-item"><a class="nav-link active cursor-pointer" onclick="filterSlots('car')" id="tab-car">Cars</a></li>
                    <li class="nav-item"><a class="nav-link cursor-pointer" onclick="filterSlots('bike')" id="tab-bike">Bikes</a></li>
                    <li class="nav-item"><a class="nav-link cursor-pointer" onclick="filterSlots('bus')" id="tab-bus">Buses</a></li>
                </ul>

                <div id="slotGridContainer" class="d-flex flex-wrap gap-3 justify-content-center border p-4 rounded-4 bg-light min-vh-25 shadow-inner">
                    <!-- Slots will be loaded here -->
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div id="bookingCard" class="card p-4 shadow-lg border-0 rounded-4 d-none">
                <h5 class="fw-bold mb-3">Spot Booking</h5>
                
                <div class="bg-light p-3 rounded-4 mb-4">
                    <p class="text-muted small mb-1">Selected Slots:</p>
                    <div id="selectedSlotName" class="fw-bold text-dark"></div>
                </div>
                
                <form id="manualBookingForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Customer Name</label>
                        <input type="text" class="form-control rounded-3" name="customer_name" required placeholder="John Doe">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Customer Phone</label>
                        <input type="tel" class="form-control rounded-3" name="customer_phone" required placeholder="+91 0000000000">
                    </div>
                    
                    <div class="p-3 rounded-4 mb-4 border-0 bg-primary shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-white opacity-75 fw-bold">Total Slots:</span>
                            <span id="displayCount" class="small fw-bold text-white">0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-white opacity-75 fw-bold">Total Amount:</span>
                            <span id="displayPrice" class="h4 fw-bold text-white mb-0">₹0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 rounded-4 fw-bold shadow-sm" id="confirmBtn">Confirm Spot Booking</button>
                    <button type="button" class="btn btn-link btn-sm w-100 mt-2 text-muted text-decoration-none" onclick="deselectAllSlots()">Cancel Selection</button>
                </form>
            </div>

            <div id="instructionCard" class="card p-4 bg-light text-center border-0 rounded-4">
                <i class="bi bi-info-circle fs-2 text-muted mb-3"></i>
                <h6 class="fw-bold">Manual Entry</h6>
                <p class="small text-muted mb-0">Click on any available (blue) slot in the grid to start a manual booking for a walk-in customer.</p>
            </div>
        </div>
    </div>
</div>

<!-- Closure Modal -->
<div class="modal fade" id="closureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Schedule Parking Closure</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-3">Confirm Closure</h6>
                <p class="text-muted small">You are about to schedule this parking lot for permanent removal. All existing bookings up to the closure date will be honored, but no new bookings will be accepted past the closure date.</p>
                
                <div class="bg-light p-3 rounded-3 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Active Bookings Impacted:</span>
                        <span class="fw-bold" id="closureActiveBookings">
                            <span class="spinner-border spinner-border-sm text-muted"></span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Revenue Impact:</span>
                        <span class="fw-bold text-danger" id="closureRevenue">
                            <span class="spinner-border spinner-border-sm text-muted"></span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Scheduled Closure Date:</span>
                        <span class="fw-bold text-primary" id="closureDateDisplay">
                            <span class="spinner-border spinner-border-sm text-muted"></span>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Reason for closure (Optional)</label>
                    <textarea id="closureReason" class="form-control rounded-3" rows="2"></textarea>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="closureCheckbox">
                    <label class="form-check-label small text-muted" for="closureCheckbox">
                        I understand that this action cannot be easily undone and my parking lot will be permanently removed on the scheduled date.
                    </label>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-danger">Type "CONFIRM REMOVE" to proceed</label>
                    <input type="text" id="closureConfirmText" class="form-control border-danger rounded-3" autocomplete="off">
                </div>

                <button class="btn btn-danger w-100 rounded-3 py-2 fw-bold" id="submitClosureBtn" disabled onclick="submitClosure()">Schedule Removal</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function getStartAndEndDateTime() {
        const date = document.getElementById('manageDate').value;
        const time = document.getElementById('manageTime').value;
        const durationMins = parseInt(document.getElementById('manageDuration').value);
        
        if (!date || !time) return null;
        
        const start = new Date(`${date}T${time}:00`);
        const end = new Date(start.getTime() + durationMins * 60000);
        
        const formatDt = (dt) => {
            const pad = n => n.toString().padStart(2, '0');
            return `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())} ${pad(dt.getHours())}:${pad(dt.getMinutes())}:00`;
        };
        
        return {
            start_datetime: formatDt(start),
            end_datetime: formatDt(end),
            duration_mins: durationMins,
            start_obj: start
        };
    }

    function isSlotExpired(startObj) {
        if (!startObj) return false;
        const now = new Date();
        return startObj < now;
    }

    const parkingId = '{{ $parkingLot->_id }}';
    const prices = {
        car: {{ $parkingLot->car_price }},
        bike: {{ $parkingLot->bike_price }},
        bus: {{ $parkingLot->bus_price }}
    };

    let allSlots = [];
    let currentFilter = 'car';
    let selectedSlots = []; 

    document.addEventListener('DOMContentLoaded', () => {
        loadSlots();

        document.getElementById('manageDate').addEventListener('change', loadSlots);
        document.getElementById('manageTime').addEventListener('change', loadSlots);
        document.getElementById('manageDuration').addEventListener('change', loadSlots);

        document.getElementById('manualBookingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (selectedSlots.length === 0) return alert('Please select at least one slot.');

            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.parking_lot_id = parkingId;
            data.slot_ids = selectedSlots.map(s => s.id);
            const dt = getStartAndEndDateTime();
            if (!dt) return alert("Invalid date or time.");
            
            data.date = document.getElementById('manageDate').value; // Keep for backward compatibility if needed
            data.start_datetime = dt.start_datetime;
            data.end_datetime = dt.end_datetime;

            try {
                const response = await fetch('/api/owner/manual-booking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    alert('Spot booking successful!');
                    deselectAllSlots();
                    loadSlots();
                } else {
                    const err = await response.json();
                    alert('Error: ' + err.message);
                }
            } catch (err) {
                console.error(err);
                alert('Something went wrong.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Confirm Spot Booking';
            }
        });
    });

    async function loadSlots() {
        const dt = getStartAndEndDateTime();
        const container = document.getElementById('slotGridContainer');
        
        if (!dt) {
            container.innerHTML = '<p class="text-danger my-4">Please select a valid date and time.</p>';
            return;
        }

        container.innerHTML = '<div class="spinner-border text-dark my-4"></div>';

        try {
            const res = await fetch(`/api/parking-lots/${parkingId}/slots?start_datetime=${encodeURIComponent(dt.start_datetime)}&end_datetime=${encodeURIComponent(dt.end_datetime)}`);
            const data = await res.json();
            allSlots = data.slots || [];
            filterSlots(currentFilter);
        } catch (err) {
            container.innerHTML = '<p class="text-danger my-4">Failed to load slots.</p>';
        }
    }

    window.filterSlots = function(type) {
        currentFilter = type;
        document.querySelectorAll('#vehicleTabs .nav-link').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + type).classList.add('active');

        const filtered = allSlots.filter(s => s.vehicle_type === type);
        const container = document.getElementById('slotGridContainer');
        
        if (filtered.length === 0) {
            container.innerHTML = `<p class="text-muted my-4 w-100 text-center">No ${type} slots configured.</p>`;
            return;
        }

        const dt = getStartAndEndDateTime();
        const isExpired = dt ? isSlotExpired(dt.start_obj) : false;

        container.innerHTML = filtered.map(slot => {
            const slotId = slot.id || slot._id;
            const isBooked = slot.is_booked || isExpired;
            const isSelected = selectedSlots.some(s => s.id === slotId);
            const statusClass = isExpired ? 'slot-expired' : (slot.is_booked ? 'slot-booked' : (isSelected ? 'slot-selected' : 'slot-available'));
            const attr = isBooked ? '' : `onclick="toggleSlot('${slotId}', '${slot.slot_number}', '${slot.vehicle_type}')"`;
            const tooltipAttr = isExpired ? 'title="Booking time expired"' : '';
            
            const watermark = `<span class="slot-watermark">${isExpired ? 'EXPIRED' : slot.vehicle_type}</span>`;
            return `<div class="slot-box ${statusClass} position-relative" id="slot-${slotId}" ${attr} ${tooltipAttr}>
                        ${slot.slot_number}
                        ${watermark}
                    </div>`;
        }).join('');
    }

    window.toggleSlot = function(id, name, type) {
        // EXACT ID COMPARISON
        const index = selectedSlots.findIndex(s => s.id === id);
        const el = document.getElementById('slot-' + id);

        if (index > -1) {
            selectedSlots.splice(index, 1);
            if (el) {
                el.classList.remove('slot-selected');
                el.classList.add('slot-available');
            }
        } else {
            selectedSlots.push({id, name, type});
            if (el) {
                el.classList.add('slot-selected');
                el.classList.remove('slot-available');
            }
        }

        updateSidebar();
    }

    function updateSidebar() {
        const summaryCard = document.getElementById('bookingCard');
        const instructionCard = document.getElementById('instructionCard');

        if (selectedSlots.length === 0) {
            summaryCard.classList.add('d-none');
            instructionCard.classList.remove('d-none');
            return;
        }

        summaryCard.classList.remove('d-none');
        instructionCard.classList.add('d-none');

        const names = selectedSlots.map(s => `<span class="badge bg-white text-dark border me-1 mb-1">${s.name}</span>`).join('');
        
        const dt = getStartAndEndDateTime();
        const durationMins = dt ? dt.duration_mins : 0;
        const multiplier = durationMins / 60;
        
        const totalPrice = selectedSlots.reduce((sum, s) => sum + (prices[s.type] * multiplier), 0);

        document.getElementById('selectedSlotName').innerHTML = names;
        document.getElementById('displayCount').innerText = selectedSlots.length;
        document.getElementById('displayPrice').innerText = '₹' + totalPrice;
    }

    function deselectAllSlots() {
        selectedSlots = [];
        document.querySelectorAll('.slot-selected').forEach(el => {
            el.classList.remove('slot-selected');
            el.classList.add('slot-available');
        });
        document.getElementById('bookingCard').classList.add('d-none');
        document.getElementById('instructionCard').classList.remove('d-none');
        document.getElementById('manualBookingForm').reset();
    }

    // Keep dropdown open when clicking the toggle label
    document.getElementById('toggleBookingsBtn').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Toggle Bookings when checkbox actually changes
    document.getElementById('toggleBookingsSwitch').addEventListener('change', async function(e) {
        const textLabel = document.getElementById('toggleBookingsText');
        const newState = this.checked;
        
        try {
            const res = await fetch(`/api/owner/parking-lots/${parkingId}/toggle-bookings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ is_accepting_bookings: newState })
            });
            const data = await res.json();
            
            if (res.ok) {
                textLabel.innerText = data.is_accepting_bookings ? 'Accepting Bookings' : 'Bookings Paused';
            } else {
                alert(data.message || 'Failed to toggle bookings.');
                this.checked = !newState; // revert
            }
        } catch (err) {
            console.error(err);
            this.checked = !newState; // revert
        }
    });

    // Schedule Closure Flow
    let closureModalInstance;
    function openClosureModal() {
        if (!closureModalInstance) {
            closureModalInstance = new bootstrap.Modal(document.getElementById('closureModal'));
        }
        closureModalInstance.show();

        // Reset form
        document.getElementById('closureCheckbox').checked = false;
        document.getElementById('closureConfirmText').value = '';
        validateClosureForm();

        // Fetch summary
        fetch(`/api/owner/parking-lots/${parkingId}/closure-summary`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('closureActiveBookings').innerText = data.active_bookings_count;
                document.getElementById('closureRevenue').innerText = '₹' + data.revenue_impact;
                document.getElementById('closureDateDisplay').innerText = data.scheduled_removal_date;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('closureActiveBookings').innerText = 'Error';
                document.getElementById('closureRevenue').innerText = 'Error';
                document.getElementById('closureDateDisplay').innerText = 'Error';
            });
    }

    document.getElementById('closureCheckbox').addEventListener('change', validateClosureForm);
    document.getElementById('closureConfirmText').addEventListener('input', validateClosureForm);

    function validateClosureForm() {
        const checkbox = document.getElementById('closureCheckbox').checked;
        const text = document.getElementById('closureConfirmText').value.trim();
        const btn = document.getElementById('submitClosureBtn');

        if (checkbox && text.toUpperCase() === 'CONFIRM REMOVE') {
            btn.disabled = false;
        } else {
            btn.disabled = true;
        }
    }

    async function submitClosure() {
        const btn = document.getElementById('submitClosureBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Scheduling...';

        const reason = document.getElementById('closureReason').value;

        try {
            const res = await fetch(`/api/owner/parking-lots/${parkingId}/schedule-closure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: reason })
            });

            const data = await res.json();
            
            if (res.ok) {
                alert('Closure scheduled successfully. Scheduled removal date: ' + data.scheduled_removal_date);
                closureModalInstance.hide();
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = 'Schedule Removal';
            }
        } catch (err) {
            console.error(err);
            alert('Something went wrong.');
            btn.disabled = false;
            btn.innerHTML = 'Schedule Removal';
        }
    }

    // Cancel Closure
    async function cancelClosure() {
        if (!confirm('Are you sure you want to cancel the scheduled closure? This will restore the parking lot to active status.')) return;
        
        try {
            const res = await fetch(`/api/owner/parking-lots/${parkingId}/cancel-closure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (res.ok) {
                alert('Closure cancelled successfully. Parking lot is now active.');
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Something went wrong.');
        }
    }
</script>
@endpush

@push('styles')
<style>
    :root {
        --navy-blue: #000080;
        --light-blue: #E6F0FF;
    }

    .nav-pills-custom .nav-link {
        color: var(--navy-blue) !important;
        background: var(--light-blue);
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 700;
        margin-right: 8px;
        border: 2px solid var(--navy-blue);
        transition: all 0.2s;
    }

    .nav-pills-custom .nav-link.active {
        background: var(--navy-blue) !important;
        color: white !important;
    }

    .slot-box {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .slot-available {
        border: 2px solid var(--navy-blue);
        background-color: var(--light-blue);
        color: var(--navy-blue);
    }

    .slot-available:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 128, 0.2);
    }

    .slot-selected {
        background-color: #000 !important;
        color: #fff !important;
        border: 2px solid #000 !important;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        transform: scale(1.05);
    }

    .slot-booked {
        background-color: #f1f5f9 !important;
        color: #cbd5e0 !important;
        border: 2px solid #e2e8f0 !important;
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
    }

    .slot-expired {
        background-color: #f8fafc !important;
        color: #cbd5e0 !important;
        border: 2px dashed #cbd5e0 !important;
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }

    .slot-watermark {
        position: absolute;
        font-size: 0.5rem;
        bottom: 4px;
        right: 4px;
        opacity: 0.8;
        text-transform: uppercase;
        pointer-events: none;
        font-weight: 800;
        color: var(--navy-blue);
    }

    .slot-selected .slot-watermark {
        color: rgba(255,255,255,0.6);
    }

    .shadow-inner {
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endpush
