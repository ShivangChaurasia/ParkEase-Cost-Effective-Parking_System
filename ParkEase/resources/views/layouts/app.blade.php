<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ParkEase - @yield('title', 'Smart Cost-Effective Parking Booking System')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet.js CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #333333;
            --primary-hover: #000000;
            --dark-bg: #FFFFFF;
            --card-bg: #F8F9FA;
            --text-light: #121212;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --border-color: #DDDDDD;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
        }

        /* Navbar */
        .navbar {
            background-color: #000000;
            border-bottom: 1px solid #333333;
        }
        
        .navbar-brand {
            font-weight: 800;
            color: #FFFFFF !important;
            letter-spacing: 1px;
        }

        .navbar-brand span {
            color: #AAAAAA;
        }

        .nav-link {
            color: #FFFFFF !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #CCCCCC !important;
        }

        .btn-primary-custom {
            background-color: var(--primary);
            border: none;
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            color: #fff;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }

        /* Form Controls */
        .form-control, .form-select {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            color: #000;
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background-color: #FFFFFF;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.1);
            color: #000;
        }

        .form-control::placeholder {
            color: #999;
        }

        /* Slot Layout Colors (Movie Theater Style) */
        .slot-box {
            width: 45px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #444;
        }
        
        .slot-available {
            background-color: #E9ECEF;
            color: #333;
            border: 1px solid #CCC;
        }
        
        .slot-available:hover {
            background-color: var(--primary);
            color: #FFF;
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        .slot-booked {
            background-color: #F8F9FA;
            color: #ADB5BD;
            cursor: not-allowed;
            border: 1px solid #DEE2E6;
            opacity: 0.7;
        }

        .slot-selected {
            background-color: var(--primary);
            color: #fff;
            border-color: #fff;
            transform: scale(1.1);
            box-shadow: 0 0 10px var(--primary);
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Park<span>Ease</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-link-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-link-item"><a class="nav-link" href="#about">How it Works</a></li>
                </ul>
                <div class="d-flex align-items-center" id="nav-auth-container">
                    <!-- Clerk Auth will be rendered here via JS -->
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5" style="border-top: 1px solid var(--border-color); background-color: var(--glass-bg);">
        <div class="container">
            <p class="mb-0 text-muted">&copy; {{ date('Y') }} ParkEase - Cost-Effective Parking System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet.js JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- App Logic & Clerk Setup -->
    <script
        data-clerk-publishable-key="{{ env('VITE_CLERK_PUBLISHABLE_KEY') }}"
        src="{{ env('CLERK_JS_URL') }}"
        type="text/javascript"
    ></script>

    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            await Clerk.load();

            const authContainer = document.getElementById('nav-auth-container');

            if (Clerk.user) {
                // User is signed in
                @auth
                    const currentRole = '{{ auth()->user()->role }}';
                    const hasCompletedOnboarding = '{{ auth()->user()->onboarding_completed }}' === '1' || '{{ auth()->user()->onboarding_completed }}' === 'true';
                    
                    if (hasCompletedOnboarding) {
                        const dashboardLink = document.createElement('a');
                        dashboardLink.href = "/dashboard";
                        dashboardLink.className = "btn btn-outline-light me-3";
                        dashboardLink.innerText = "Dashboard";
                        authContainer.appendChild(dashboardLink);

                        if (currentRole === 'owner') {
                            const ownerDashboardLink = document.createElement('a');
                            ownerDashboardLink.href = "/owner/dashboard";
                            ownerDashboardLink.className = "btn btn-outline-light me-3";
                            ownerDashboardLink.innerText = "Owner Dashboard";
                            authContainer.appendChild(ownerDashboardLink);

                            const roleToggleBtn = document.createElement('a');
                            roleToggleBtn.href = "/switch-role";
                            roleToggleBtn.className = "btn btn-warning me-3 btn-sm fw-bold";
                            roleToggleBtn.innerText = "Switch to User";
                            authContainer.appendChild(roleToggleBtn);
                        } else {
                            const roleToggleBtn = document.createElement('a');
                            roleToggleBtn.href = "/switch-role";
                            roleToggleBtn.className = "btn btn-warning me-3 btn-sm fw-bold";
                            roleToggleBtn.innerText = "Become Host";
                            authContainer.appendChild(roleToggleBtn);
                        }
                    }
                @else
                    const dashboardLink = document.createElement('a');
                    dashboardLink.href = "/dashboard";
                    dashboardLink.className = "btn btn-outline-light me-3";
                    dashboardLink.innerText = "Dashboard";
                    authContainer.appendChild(dashboardLink);
                @endauth

                @auth
                    if (currentRole === 'owner' && '{{ auth()->user()->photo_path }}') {
                        const hostPhoto = document.createElement('img');
                        hostPhoto.src = '/{{ auth()->user()->photo_path }}';
                        hostPhoto.className = 'rounded-circle me-3 border border-2 border-primary';
                        hostPhoto.style.width = '36px';
                        hostPhoto.style.height = '36px';
                        hostPhoto.style.objectFit = 'cover';
                        authContainer.appendChild(hostPhoto);
                    }
                @endauth
                
                const profileLink = document.createElement('a');
                profileLink.href = "/settings";
                profileLink.className = "d-flex align-items-center text-decoration-none ms-2";
                profileLink.title = "Account Settings";
                
                const avatar = document.createElement('img');
                avatar.src = Clerk.user.imageUrl;
                avatar.className = "rounded-circle border border-2 border-light shadow-sm";
                avatar.style.width = "40px";
                avatar.style.height = "40px";
                avatar.style.objectFit = "cover";
                
                profileLink.appendChild(avatar);
                authContainer.appendChild(profileLink);

                // Sync with Laravel Backend
                if (!sessionStorage.getItem('clerk_synced')) {
                    const token = await Clerk.session.getToken();
                    try {
                        const response = await fetch('/api/auth/clerk-sync', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                clerk_id: Clerk.user.id,
                                email: Clerk.user.primaryEmailAddress ? Clerk.user.primaryEmailAddress.emailAddress : '',
                                first_name: Clerk.user.firstName || '',
                                last_name: Clerk.user.lastName || ''
                            })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            sessionStorage.setItem('clerk_synced', 'true');
                            if (data.redirect && window.location.pathname !== data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload(); // Reload to apply Laravel Auth state to UI
                            }
                        }
                    } catch (e) {
                        console.error('Failed to sync with backend', e);
                    }
                } else {
                    if (typeof renderClerkComponent === 'function') {
                        renderClerkComponent();
                    }
                }

            } else {
                // User is not signed in
                sessionStorage.removeItem('clerk_synced');
                authContainer.innerHTML = `
                    <a href="/login" class="btn btn-outline-light me-2">Login</a>
                    <a href="/register" class="btn btn-primary-custom">Register</a>
                `;
                
                if (typeof renderClerkComponent === 'function') {
                    renderClerkComponent();
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
