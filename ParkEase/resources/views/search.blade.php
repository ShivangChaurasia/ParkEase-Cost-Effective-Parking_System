@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="container-fluid py-4 h-100">
    <div class="row min-vh-75">
        <!-- Parking List -->
        <div class="col-lg-5 col-md-6 mb-4 overflow-auto" style="max-height: 80vh;" id="parkingList">
            <h3 class="fw-bold mb-4">Available Parking Slots</h3>
            
            <div id="loadingIndicator" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Finding the best spots...</p>
            </div>

            <div id="resultsContainer">
                <!-- JavaScript will populate cards here -->
            </div>
            
            <div id="noResults" class="text-center py-5 d-none">
                <h4 class="text-muted">No parking found nearby.</h4>
                <p class="small text-muted">Try a different location or pincode.</p>
            </div>
        </div>

        <!-- Map Container -->
        <div class="col-lg-7 col-md-6">
            <div id="map" style="width: 100%; height: 80vh; border-radius: 15px; border: 1px solid var(--border-color); box-shadow: 0 10px 20px rgba(0,0,0,0.3);"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Get query params
    const urlParams = new URLSearchParams(window.location.search);
    const pincode = urlParams.get('pincode');
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');

    // Initialize Map
    let map = L.map('map').setView([20.5937, 78.9629], 5); // Default to India center
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    let markers = [];
    let bounds = new L.LatLngBounds();

    if (lat && lng) {
        // Add blue pointer for current location like Google Maps
        L.circleMarker([lat, lng], {
            color: '#ffffff',
            fillColor: '#4285F4',
            fillOpacity: 1,
            radius: 8,
            weight: 2,
            zIndexOffset: 1000
        }).addTo(map).bindPopup("<b>You are here</b>");
        bounds.extend([lat, lng]);
    }

    // Fetch API Data
    let apiUrl = '/api/search?';
    if (pincode) apiUrl += `pincode=${pincode}`;
    if (lat && lng) apiUrl += `lat=${lat}&lng=${lng}`;

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById('loadingIndicator').classList.add('d-none');
            
            const parkings = data.data;
            if (parkings.length === 0) {
                document.getElementById('noResults').classList.remove('d-none');
                if (lat && lng) {
                    map.fitBounds(bounds, {maxZoom: 14});
                }
                return;
            }

            const container = document.getElementById('resultsContainer');

            parkings.forEach(parking => {
                // Add Marker
                let marker = L.marker([parking.latitude, parking.longitude]).addTo(map);
                marker.bindPopup(`<b>${parking.name}</b><br>${parking.address}<br>₹${parking.price_per_slot}/slot`);
                markers.push(marker);
                bounds.extend([parking.latitude, parking.longitude]);

                // Add List Card
                const card = document.createElement('div');
                card.className = 'card mb-3 p-3 shadow-sm parking-card';
                card.style.cursor = 'pointer';
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--primary);">${parking.name}</h5>
                            <p class="text-muted small mb-2">${parking.address}, ${parking.city} - ${parking.pincode}</p>
                            <span class="badge bg-dark border text-light">₹${parking.price_per_slot} / Slot</span>
                        </div>
                        <div>
                            <a href="/parking/${parking._id}" class="btn btn-primary-custom btn-sm">Book Now</a>
                        </div>
                    </div>
                `;
                
                // Highlight marker on hover
                card.addEventListener('mouseenter', () => marker.openPopup());
                container.appendChild(card);
            });

            // Fit map to markers
            if(parkings.length > 0 || (lat && lng)) {
                map.fitBounds(bounds, {padding: [50, 50], maxZoom: 14});
            }
        })
        .catch(err => {
            console.error('Error fetching parking:', err);
            document.getElementById('loadingIndicator').innerHTML = '<p class="text-danger">Failed to load data.</p>';
        });
</script>

<style>
    .parking-card {
        transition: all 0.2s;
    }
    .parking-card:hover {
        transform: translateX(10px);
        border-left: 4px solid var(--primary) !important;
    }
</style>
@endpush
