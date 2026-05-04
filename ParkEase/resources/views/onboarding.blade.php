@extends('layouts.app')

@section('title', 'Welcome to ParkEase - Select Your Role')

@section('content')
<div class="container mt-5 py-5">
    <div class="row justify-content-center text-center mb-5">
        <div class="col-md-8">
            <h1 class="fw-bold mb-3">Welcome to ParkEase! 👋</h1>
            <p class="text-muted lead">Before we get started, please tell us how you plan to use ParkEase. You can always switch later.</p>
        </div>
    </div>

    <div class="row justify-content-center g-4">
        <!-- User Role Selection -->
        <div class="col-md-5">
            <div class="card h-100 p-5 text-center role-card shadow-sm" style="cursor: pointer; border: 2px solid transparent; transition: all 0.3s;" id="card-user" onclick="selectRole('user')">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="var(--primary)" class="bi bi-car-front mb-4 mx-auto" viewBox="0 0 16 16">
                  <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0m10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M6 5.9a1 1 0 0 0 1-.9c0-.4 0-.8.1-1.2.1-.5.3-1 .6-1.5A3 3 0 0 1 10.3 1c.5 0 1 .1 1.4.3l.1.1a4 4 0 0 1 1.6 2.3c.3.9.5 1.9.5 2.9v1.2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5.8c0-1 .2-2 .5-2.9A4 4 0 0 1 4.1 1.6a3 3 0 0 1 1.4-.4l.1-.1a3 3 0 0 1 2.6 1.3c.3.5.5 1 .6 1.5.1.4.1.8.1 1.2a1 1 0 0 0 1 .9m6 .9v-.8a3 3 0 0 0-.4-2.1A2 2 0 0 0 10.3 2a2 2 0 0 0-1.2.2A2 2 0 0 0 7.8 3a3 3 0 0 0-.5 2v.8h4.7zm-9.3.8H2.4a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h11.2a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5H12.7l-1 2.5a.5.5 0 0 1-.9.3l-1-2.8H5.2l-1 2.8a.5.5 0 0 1-.9-.3l-1-2.5z"/>
                </svg>
                <h3 class="fw-bold mb-2">I want to Park</h3>
                <p class="text-muted">Find and book secure, cost-effective parking spots nearby instantly.</p>
            </div>
        </div>

        <!-- Owner Role Selection -->
        <div class="col-md-5">
            <div class="card h-100 p-5 text-center role-card shadow-sm" style="cursor: pointer; border: 2px solid transparent; transition: all 0.3s;" id="card-owner" onclick="selectRole('owner')">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="var(--primary)" class="bi bi-building mb-4 mx-auto" viewBox="0 0 16 16">
                  <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                  <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/>
                </svg>
                <h3 class="fw-bold mb-2">I want to Host</h3>
                <p class="text-muted">Register your empty space and start earning by hosting parkers.</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <button id="continueBtn" class="btn btn-primary-custom w-100 py-3 fs-5" disabled>Continue</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #DDDDDD !important;
    }
    .role-card.selected {
        border-color: var(--primary) !important;
        background-color: #f8f9fa;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let selectedRole = null;

    function selectRole(role) {
        selectedRole = role;
        
        // Update UI
        document.querySelectorAll('.role-card').forEach(el => el.classList.remove('selected'));
        document.getElementById('card-' + role).classList.add('selected');
        
        // Enable button
        document.getElementById('continueBtn').disabled = false;
    }

    document.getElementById('continueBtn').addEventListener('click', function() {
        if (!selectedRole) return;
        
        const btn = this;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        btn.disabled = true;

        fetch('/api/onboarding', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: selectedRole })
        })
        .then(res => res.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Something went wrong.");
            btn.innerHTML = 'Continue';
            btn.disabled = false;
        });
    });
</script>
@endpush
