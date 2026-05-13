@extends('layouts.app')

@section('title', 'Owner Dashboard')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Owner Dashboard</h2>
            <p class="text-muted">Manage your parking areas and view bookings.</p>
        </div>
    </div>

    <!-- Host Profile Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4 d-flex flex-row align-items-center gap-4">
                @if(auth()->user()->photo_path)
                    <img src="/{{ auth()->user()->photo_path }}" alt="Host Photo" class="rounded-circle border border-3 border-dark" style="width: 80px; height: 80px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 24px;">
                        <i class="bi bi-person"></i>
                    </div>
                @endif
                <div>
                    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted mb-0 small">Phone: {{ auth()->user()->phone }} | Aadhaar: **** **** {{ substr(auth()->user()->aadhaar_no, -4) }}</p>
                </div>
                <div class="ms-auto">
                    <a href="/owner/kyc" class="btn btn-outline-dark btn-sm">Edit KYC Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Cards -->
    <!-- Analytics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-4 text-center shadow-sm border-0 bg-white">
                <h6 class="text-muted text-uppercase fw-bold ls-1 small">Properties</h6>
                <h2 class="fw-bold mb-0 text-dark">{{ $totalParkingLots }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center shadow-sm border-0 bg-white">
                <h6 class="text-muted text-uppercase fw-bold ls-1 small">Total Slots</h6>
                <h2 class="fw-bold mb-0 text-dark">{{ $totalSlots }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center shadow-sm border-0 bg-primary-subtle">
                <h6 class="text-primary text-uppercase fw-bold ls-1 small">Active Bookings</h6>
                <h2 class="fw-bold mb-0 text-primary">{{ $activeBookingsCount }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center shadow-sm border-0 bg-success-subtle">
                <h6 class="text-success text-uppercase fw-bold ls-1 small">Total Earnings</h6>
                <h2 class="fw-bold mb-0 text-success">₹{{ number_format($totalEarnings, 2) }}</h2>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm mb-5 border" id="ownerDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-3 rounded-3" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties" type="button" role="tab">My Parking Areas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3 rounded-3" id="financials-tab" data-bs-toggle="tab" data-bs-target="#financials" type="button" role="tab">Earnings & Financials</button>
        </li>
    </ul>

    <div class="tab-content" id="ownerDashboardTabsContent">
        <!-- Properties Management -->
        <div class="tab-pane fade show active" id="properties" role="tabpanel">
            <div class="row">
                <!-- Add Parking Lot Form -->
                <div class="col-lg-7 mb-5">
                    <div class="card p-4 border-0 shadow-sm rounded-4">
                        <h4 class="mb-4 fw-bold">Register New Parking Area</h4>
                        <form id="addParkingForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Parking Name</label>
                                    <input type="text" class="form-control" name="name" required placeholder="e.g. City Center Parking">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" class="form-control" name="pincode" required>
                                </div>
                                
                                <div class="col-md-12 mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold mb-0">Location (Click map to pin)</label>
                                        <button type="button" class="btn btn-sm btn-outline-dark" id="detectLocationBtn">
                                            <i class="bi bi-geo-alt-fill"></i> Detect My Location
                                        </button>
                                    </div>
                                    <div id="ownerMap" style="height: 300px; border-radius: 8px; border: 1px solid var(--border-color);"></div>
                                    <input type="hidden" name="latitude" id="latInput" required>
                                    <input type="hidden" name="longitude" id="lngInput" required>
                                    <div class="text-muted small mt-1">Latitude: <span id="latDisplay">-</span>, Longitude: <span id="lngDisplay">-</span></div>
                                </div>

                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Car Price (₹/Slot)</label>
                                    <input type="number" class="form-control" name="car_price" min="0" required>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Bike Price (₹/Slot)</label>
                                    <input type="number" class="form-control" name="bike_price" min="0" required>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Bus Price (₹/Slot)</label>
                                    <input type="number" class="form-control" name="bus_price" min="0" required>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Opening Time</label>
                                    <input type="time" class="form-control" name="opening_time" required>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Closing Time</label>
                                    <input type="time" class="form-control" name="closing_time" required>
                                </div>

                                <div class="col-12 mt-4">
                                    <label class="form-label fw-bold">Slot Configuration</label>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Car Slots</label>
                                    <input type="number" class="form-control" name="car_slots" min="0" value="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bike Slots</label>
                                    <input type="number" class="form-control" name="bike_slots" min="0" value="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bus Slots</label>
                                    <input type="number" class="form-control" name="bus_slots" min="0" value="0" required>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary-custom w-100 py-3 rounded-3 shadow" id="submitBtn">Register Parking Area</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Registered Parking Lots -->
                <div class="col-lg-5">
                    <h4 class="mb-4 fw-bold">Your Parking Areas</h4>
                    @if($parkingLots->isEmpty())
                        <div class="alert alert-light border text-center p-4">
                            <p class="mb-0 text-muted">You haven't registered any parking areas yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($parkingLots as $lot)
                                <div class="card p-3 border-0 shadow-sm rounded-4">
                                    <h5 class="fw-bold mb-1">{{ $lot->name }}</h5>
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-primary"></i> {{ $lot->address }}, {{ $lot->city }} - {{ $lot->pincode }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-light text-dark border">Car: ₹{{ $lot->car_price ?? 0 }}</span>
                                            <span class="badge bg-light text-dark border">Bike: ₹{{ $lot->bike_price ?? 0 }}</span>
                                        </div>
                                        <span class="small text-muted">{{ $lot->opening_time }} - {{ $lot->closing_time }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <a href="/owner/parking/{{ $lot->_id }}/manage" class="btn btn-dark btn-sm w-100 rounded-3 py-2 fw-bold">Manage & Spot Book</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Financial Transactions -->
        <div class="tab-pane fade" id="financials" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">Transaction History</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3">Transaction ID</th>
                                        <th class="py-3">Date</th>
                                        <th class="py-3">Customer</th>
                                        <th class="py-3 text-center">Type</th>
                                        <th class="py-3 text-end">Amount</th>
                                        <th class="py-3 text-center px-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                        <tr>
                                            <td class="px-4 py-3 fw-mono text-muted small">#{{ substr($tx->_id, -12) }}</td>
                                            <td class="py-3 text-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                            <td class="py-3">
                                                <div class="fw-bold">{{ $tx->description }}</div>
                                            </td>
                                            <td class="py-3 text-center">
                                                <span class="badge {{ $tx->type === 'earning' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-3">
                                                    {{ strtoupper($tx->type) }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-end fw-bold {{ $tx->type === 'earning' ? 'text-success' : 'text-danger' }}">
                                                {{ $tx->type === 'earning' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                                            </td>
                                            <td class="py-3 text-center px-4">
                                                <span class="badge {{ $tx->status === 'completed' ? 'bg-success' : ($tx->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ ucfirst($tx->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center p-5 text-muted">
                                                <i class="bi bi-wallet2 fs-1 mb-2 d-block"></i>
                                                No transactions recorded yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Map
        const map = L.map('ownerMap').setView([20.5937, 78.9629], 5); // Center of India
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker;

        // Try to get user's location to center map
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                map.setView([position.coords.latitude, position.coords.longitude], 12);
            });
        }

        // Detect Location Button
        document.getElementById('detectLocationBtn').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Detecting...';
            btn.disabled = true;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    const latlng = [lat, lng];
                    map.setView(latlng, 15);
                    
                    document.getElementById('latInput').value = lat;
                    document.getElementById('lngInput').value = lng;
                    document.getElementById('latDisplay').innerText = lat.toFixed(6);
                    document.getElementById('lngDisplay').innerText = lng.toFixed(6);

                    if (marker) {
                        marker.setLatLng(latlng);
                    } else {
                        marker = L.marker(latlng).addTo(map);
                    }
                    
                    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Found!';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 3000);
                }, function(error) {
                    alert('Error detecting location. Please ensure location services are enabled in your browser.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            } else {
                alert('Geolocation is not supported by your browser.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            document.getElementById('latDisplay').innerText = lat.toFixed(6);
            document.getElementById('lngDisplay').innerText = lng.toFixed(6);

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

        // Form Submit
        document.getElementById('addParkingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = 'Registering...';
            btn.disabled = true;

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Wait for Clerk token
            let token = '';
            if (window.Clerk && window.Clerk.session) {
                token = await window.Clerk.session.getToken();
            }

            try {
                const response = await fetch('/api/owner/parking-lots', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    alert('Parking area registered successfully!');
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert('Error: ' + (errorData.message || 'Validation failed'));
                    btn.innerHTML = 'Register Parking Area';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Something went wrong.');
                btn.innerHTML = 'Register Parking Area';
                btn.disabled = false;
            }
        });
    });
</script>
@endpush
