@extends('layouts.app')

@section('title', 'Find Parking Nearby')

@push('styles')
<style>
    .search-layout {
        height: calc(100vh - 72px);
        overflow: hidden;
    }

    #map {
        height: 100%;
        width: 100%;
        z-index: 1;
    }

    .search-overlay {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        width: 400px;
        max-height: calc(100vh - 112px);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .glass-search-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        padding: 24px;
        overflow-y: auto;
    }

    .result-card {
        border-radius: 16px;
        border: 1px solid #f0f0f0;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        background: white;
        cursor: pointer;
    }

    .result-card:hover {
        border-color: #000;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    .result-card.active {
        border-color: #000;
        background: #f8f9fa;
    }

    .distance-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        background: #f0f0f0;
        border-radius: 6px;
        color: #666;
    }

    .price-tag {
        font-weight: 800;
        font-size: 1.1rem;
    }

    /* Custom Scrollbar */
    .glass-search-card::-webkit-scrollbar { width: 4px; }
    .glass-search-card::-webkit-scrollbar-track { background: transparent; }
    .glass-search-card::-webkit-scrollbar-thumb { background: #eee; border-radius: 10px; }

    @media (max-width: 576px) {
        .search-overlay {
            width: calc(100vw - 40px);
            left: 20px;
            top: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="search-layout position-relative">
    <!-- Map as Background -->
    <div id="map"></div>

    <!-- Floating Search UI -->
    <div class="search-overlay">
        <!-- Result List Card -->
        <div class="glass-search-card flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Nearby Parking</h5>
                <span id="resultCount" class="badge bg-dark rounded-pill">0 Results</span>
            </div>

            <div id="loadingIndicator" class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-dark" role="status"></div>
                <p class="small text-muted mt-2 fw-bold">Optimizing routes...</p>
            </div>

            <div id="resultsContainer">
                <!-- JS Populated -->
            </div>

            <div id="noResults" class="text-center py-5 d-none">
                <i class="bi bi-geo-alt-fill text-muted fs-1 mb-3"></i>
                <h6 class="fw-bold">No spots found here</h6>
                <p class="small text-muted">Try searching another area or pincode.</p>
                <a href="/" class="btn btn-sm btn-dark rounded-pill px-4">Change Location</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const pincode = urlParams.get('pincode');
    const lat = urlParams.get('lat');
    const lng = urlParams.get('lng');
    const isLoggedIn = @json(Auth::check());

    // Initialize Map with dark/modern tiles if possible, or clean standard
    let map = L.map('map', {
        zoomControl: false // Hide zoom control for cleaner UI
    }).setView([20.5937, 78.9629], 5);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    // Reposition zoom control to bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    let markers = {};
    let bounds = new L.LatLngBounds();

    if (lat && lng) {
        L.circleMarker([lat, lng], {
            color: '#fff',
            fillColor: '#000',
            fillOpacity: 1,
            radius: 8,
            weight: 3
        }).addTo(map).bindPopup("<b>Your Location</b>");
        bounds.extend([lat, lng]);
    }

    // Fetch API Data
    let apiUrl = '/api/search?';
    if (pincode) apiUrl += `pincode=${pincode}`;
    if (lat && lng) apiUrl += `lat=${lat}&lng=${lng}`;

    fetch(apiUrl)
        .then(res => res.json())
        .then(data => {
            document.getElementById('loadingIndicator').classList.add('d-none');
            const parkings = data.data;
            document.getElementById('resultCount').innerText = parkings.length + ' Results';

            if (parkings.length === 0) {
                document.getElementById('noResults').classList.remove('d-none');
                if (lat && lng) map.setView([lat, lng], 14);
                return;
            }

            const container = document.getElementById('resultsContainer');

            parkings.forEach((parking, index) => {
                const id = parking._id || parking.id;
                
                // Add Custom Marker
                const marker = L.marker([parking.latitude, parking.longitude]).addTo(map);
                marker.bindPopup(`
                    <div class="p-2">
                        <h6 class="fw-bold mb-1">${parking.name}</h6>
                        <p class="small text-muted mb-2">${parking.address}</p>
                        <a href="/parking/${id}" class="btn btn-dark btn-sm w-100 py-2 rounded-3">Select Spot</a>
                    </div>
                `);
                markers[id] = marker;
                bounds.extend([parking.latitude, parking.longitude]);

                // Add Card
                const card = document.createElement('div');
                card.className = 'result-card';
                card.id = `card-${id}`;
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="distance-badge">${parking.city || 'Nearby'}</span>
                        <div class="price-tag text-dark">₹${parking.car_price || 0}<span class="small text-muted fw-normal">/hr</span></div>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark">${parking.name}</h6>
                    <p class="small text-muted mb-3">${parking.address}</p>
                    <div class="d-flex gap-2">
                        <a href="${isLoggedIn ? '/parking/' + id : '/login?intended=/parking/' + id}" class="btn btn-dark btn-sm flex-grow-1 py-2 rounded-3 fw-bold">Book Now</a>
                        <button class="btn btn-outline-dark btn-sm px-3 rounded-3" onclick="focusParking('${id}')"><i class="bi bi-geo-alt"></i></button>
                    </div>
                `;
                
                card.addEventListener('mouseenter', () => {
                    card.classList.add('active');
                    marker.openPopup();
                });
                card.addEventListener('mouseleave', () => card.classList.remove('active'));
                
                container.appendChild(card);
            });

            if(parkings.length > 0) {
                map.fitBounds(bounds, {padding: [100, 100], maxZoom: 14});
            }
        });

    window.focusParking = function(id) {
        const marker = markers[id];
        if (marker) {
            map.setView(marker.getLatLng(), 16);
            marker.openPopup();
            document.getElementById(`card-${id}`).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };
</script>
@endpush
