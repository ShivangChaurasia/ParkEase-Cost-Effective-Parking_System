@extends('layouts.app')

@section('title', 'Find Parking')

@section('content')
<div class="container mt-5">
    <div class="row align-items-center min-vh-75 py-5">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h1 class="display-4 fw-bold mb-4">Find & Book Your Parking Slot Like a <span style="color: var(--primary);">Movie Ticket</span></h1>
            <p class="lead mb-4 text-muted">No more driving around looking for a spot. Enter your location or use GPS to find the best affiliated parking areas near you.</p>
            
            <div class="card p-4 shadow-lg border-0" style="background: var(--glass-bg);">
                <form action="/search" method="GET" id="searchForm">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Search by Pincode</label>
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="pincode" placeholder="e.g. 10001" id="pincodeInput">
                            <button class="btn btn-primary-custom px-4" type="submit">Search</button>
                        </div>
                    </div>
                    
                    <div class="text-center my-3">
                        <span class="text-muted small fw-bold">OR</span>
                    </div>
                    
                    <button type="button" id="gpsBtn" class="btn btn-outline-dark w-100 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-geo-alt-fill me-2" viewBox="0 0 16 16">
                          <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        Detect My Live Location
                    </button>
                    <!-- Hidden inputs for GPS -->
                    <input type="hidden" name="lat" id="latInput">
                    <input type="hidden" name="lng" id="lngInput">
                </form>
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <!-- Image generation prompt would be perfect here to show a premium app preview, but we'll use a placeholder or style for now -->
            <div class="position-relative">
                <div style="width: 100%; height: 400px; background: linear-gradient(135deg, #2A2A2A 0%, #1E1E1E 100%); border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); display:flex; flex-direction: column; align-items:center; justify-content:center; border: 1px solid var(--border-color);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="var(--primary)" class="bi bi-car-front-fill mb-3" viewBox="0 0 16 16">
                      <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679c.033.161.049.325.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.807.807 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17 1.247 0 3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z"/>
                    </svg>
                    <h3 class="text-white fw-bold">Smart Parking</h3>
                    <p class="text-muted">Interactive map coming in next steps</p>
                </div>
                <!-- Decorative Elements -->
                <div class="position-absolute" style="top: -20px; left: -20px; width: 100px; height: 100px; background: var(--primary); border-radius: 50%; filter: blur(50px); z-index: -1; opacity: 0.5;"></div>
                <div class="position-absolute" style="bottom: -20px; right: -20px; width: 150px; height: 150px; background: #4e54c8; border-radius: 50%; filter: blur(60px); z-index: -1; opacity: 0.3;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function useIpFallback(btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Using IP Fallback...';
        fetch('https://ipapi.co/json/')
            .then(res => res.json())
            .then(data => {
                if(data.latitude && data.longitude) {
                    document.getElementById('latInput').value = data.latitude;
                    document.getElementById('lngInput').value = data.longitude;
                    document.getElementById('pincodeInput').value = ''; 
                    document.getElementById('searchForm').submit();
                } else {
                    throw new Error("Could not detect location from IP");
                }
            })
            .catch(err => {
                alert('Location detection failed. Please enter a pincode.');
                btn.innerHTML = 'Detect My Live Location';
            });
    }

    document.getElementById('gpsBtn').addEventListener('click', function() {
        const btn = this;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Detecting...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latInput').value = position.coords.latitude;
                    document.getElementById('lngInput').value = position.coords.longitude;
                    document.getElementById('pincodeInput').value = ''; // clear pincode
                    document.getElementById('searchForm').submit();
                },
                function(error) {
                    console.log('Geolocation error:', error.message);
                    useIpFallback(btn);
                },
                { timeout: 10000 }
            );
        } else {
            console.log('Geolocation not supported');
            useIpFallback(btn);
        }
    });
</script>
@endpush
