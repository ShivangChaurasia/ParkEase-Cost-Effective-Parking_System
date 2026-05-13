@extends('layouts.app')

@section('title', 'Smart Parking Simplified')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #000000 0%, #333333 100%);
        --accent-glow: rgba(0, 0, 0, 0.1);
    }

    .hero-section {
        padding: 100px 0;
        background: radial-gradient(circle at top right, rgba(0,0,0,0.02), transparent),
                    radial-gradient(circle at bottom left, rgba(0,0,0,0.02), transparent);
    }

    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -2px;
        margin-bottom: 24px;
        background: linear-gradient(180deg, #000 0%, #444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: #666;
        max-width: 550px;
        margin-bottom: 40px;
        line-height: 1.6;
    }

    .search-card-premium {
        background: white;
        border-radius: 32px;
        padding: 40px;
        box-shadow: 0 20px 80px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }

    .search-card-premium:hover {
        transform: translateY(-5px);
    }

    .feature-card {
        padding: 32px;
        border-radius: 24px;
        background: white;
        border: 1px solid #f0f0f0;
        height: 100%;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        border-color: #000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        background: #000;
        color: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        font-size: 1.5rem;
    }

    .trust-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 8px 16px;
        background: #f8f9fa;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #444;
        margin-bottom: 24px;
    }

    .app-preview-container {
        position: relative;
        z-index: 1;
    }

    .app-preview-image {
        border-radius: 40px;
        box-shadow: 0 30px 100px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 600px;
        transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
        transition: transform 0.5s ease;
    }

    .app-preview-image:hover {
        transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
    }

    .stats-row {
        margin-top: 80px;
        padding-top: 40px;
        border-top: 1px solid #eee;
    }

    .stat-item h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .stat-item p {
        color: #888;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    @media (max-width: 991px) {
        .hero-title { font-size: 3rem; }
    }
</style>
@endpush

@section('content')
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="trust-badge">
                    <span class="d-flex gap-1 text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </span>
                    Trusted by 50,000+ drivers across India
                </div>
                
                <h1 class="hero-title">Parking Simplified,<br>Life Amplified.</h1>
                <p class="hero-subtitle">Reserved spots, live availability, and seamless payments. Experience the future of cost-effective parking management today.</p>
                
                <div class="search-card-premium">
                    <form action="/search" method="GET" id="searchForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-3">Find a spot near you</label>
                            <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden" style="border: 1px solid #eee;">
                                <span class="input-group-text bg-white border-0 ps-4 text-muted"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control border-0 px-2" name="pincode" placeholder="Enter Pincode (e.g. 10001)" id="pincodeInput" style="box-shadow: none;">
                                <button class="btn btn-dark px-5 fw-bold" type="submit">Find Parking</button>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            <hr class="flex-grow-1 opacity-10">
                            <span class="text-muted small fw-bold">OR</span>
                            <hr class="flex-grow-1 opacity-10">
                        </div>
                        
                        <button type="button" id="gpsBtn" class="btn btn-outline-dark w-100 py-3 rounded-4 fw-bold mt-3 d-flex align-items-center justify-content-center transition-all">
                            <i class="bi bi-cursor-fill me-2"></i>
                            Use Current Location
                        </button>
                        
                        <input type="hidden" name="lat" id="latInput">
                        <input type="hidden" name="lng" id="lngInput">
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6 app-preview-container text-center">
                <img src="/premium_parking_app_hero_1778667074885.png" alt="ParkEase App" class="app-preview-image">
                <div class="position-absolute" style="top: 20%; right: 0; width: 150px; height: 150px; background: rgba(0,0,0,0.05); border-radius: 50%; filter: blur(60px); z-index: -1;"></div>
            </div>
        </div>

        <div class="row stats-row g-4 text-center">
            <div class="col-6 col-md-3 stat-item">
                <h2>500+</h2>
                <p>Parking Areas</p>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <h2>12k+</h2>
                <p>Monthly Bookings</p>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <h2>₹2M+</h2>
                <p>User Savings</p>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <h2>4.9/5</h2>
                <p>User Rating</p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mb-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold h1 mb-3">Why choose ParkEase?</h2>
        <p class="text-muted">The smartest way to park your vehicle in any city.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <h4 class="fw-bold mb-3">Instant Booking</h4>
                <p class="text-muted mb-0">Book your slot in less than 30 seconds. No more waiting at gates or searching for change.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h4 class="fw-bold mb-3">Guaranteed Safety</h4>
                <p class="text-muted mb-0">All our parking lots are verified with 24/7 CCTV surveillance and secure entry/exit points.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon"><i class="bi bi-cash-stack"></i></div>
                <h4 class="fw-bold mb-3">Cost Effective</h4>
                <p class="text-muted mb-0">Save up to 30% on parking fees compared to spot booking. Pay only for the exact duration you use.</p>
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
                btn.innerHTML = '<i class="bi bi-cursor-fill me-2"></i> Use Current Location';
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
                    document.getElementById('pincodeInput').value = ''; 
                    document.getElementById('searchForm').submit();
                },
                function(error) {
                    useIpFallback(btn);
                },
                { timeout: 10000 }
            );
        } else {
            useIpFallback(btn);
        }
    });
</script>
@endpush
