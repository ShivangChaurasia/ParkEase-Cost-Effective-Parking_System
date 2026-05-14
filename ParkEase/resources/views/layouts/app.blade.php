<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ParkEase - @yield('title', 'Smart Mobility')</title>

    <!-- Theme Initialization -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet.js CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="apple-touch-icon" href="/images/favicon-180x180.png">

    <!-- Premium Design System -->
    <link rel="stylesheet" href="/css/parkease.css">
    
    <style>
        .navbar-premium {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border-default);
            padding: var(--space-3) 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all var(--transition-base);
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--text-primary) !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: var(--space-2);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .navbar-brand span {
            color: var(--brand-aqua);
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: var(--space-2) var(--space-4) !important;
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-primary) !important;
            background: var(--bg-hover);
        }

        .theme-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
            border: 1px solid var(--border-default);
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .theme-toggle-btn:hover {
            border-color: var(--brand-aqua);
            background: var(--bg-hover);
            transform: scale(1.05);
        }

        [data-theme="dark"] .theme-icon-light { display: none; }
        [data-theme="light"] .theme-icon-dark { display: none; }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
            padding: var(--space-2);
            color: var(--text-secondary);
            transition: color var(--transition-fast);
        }
        
        .notification-bell:hover {
            color: var(--text-primary);
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: var(--brand-aqua);
            border-radius: var(--radius-full);
            border: 2px solid var(--bg-surface);
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Splash Screen -->
    <div id="splash-screen" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: var(--bg-base); z-index: 9999; display: flex; align-items: center; justify-content: center; transition: opacity 0.4s ease;">
        <div style="text-align: center;">
            <img src="/images/favicon.png" style="width: 64px; height: 64px; opacity: 0.8;" alt="Loading">
        </div>
    </div>

    <!-- Premium Navbar -->
    <nav class="navbar navbar-expand-lg navbar-premium" id="mainNavbar">
        <div class="container">
            <div class="d-flex align-items-center">
                <a class="navbar-brand me-5" href="/">
                    <img src="/images/favicon.png" alt="Logo" style="width: 32px; height: 32px;">
                    Park<span>Ease</span>
                </a>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto gap-2">
                        <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Home</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('search*') ? 'active' : '' }}" href="/search">Find Parking</a></li>
                        @auth
                            <li class="nav-item"><a class="nav-link {{ request()->is('dashboard*') || request()->is('owner/dashboard*') ? 'active' : '' }}" href="{{ auth()->user()->role === 'owner' ? '/owner/dashboard' : '/dashboard' }}">Dashboard</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="d-flex align-items-center gap-4">
                <!-- Theme Toggle -->
                <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle Theme">
                    <i class="bi bi-sun theme-icon-light"></i>
                    <i class="bi bi-moon-stars theme-icon-dark"></i>
                </button>

                @auth
                <div class="notification-bell d-none d-md-block">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="notification-badge"></span>
                </div>
                @endauth

                <div id="nav-auth-container" class="d-flex align-items-center gap-3">
                    <!-- Auth elements injected via JS -->
                </div>
                
                <button class="navbar-toggler border-0 shadow-none d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <i class="bi bi-list fs-2 text-primary"></i>
                </button>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="py-5 mt-auto" style="background: var(--bg-surface); border-top: 1px solid var(--border-default);">
        <div class="container text-center">
            <h5 class="fw-bold mb-3">Park<span style="color: var(--brand-aqua);">Ease</span></h5>
            <p class="text-muted text-small mb-4">Intelligent urban SaaS for smart mobility.</p>
            <div class="d-flex justify-content-center gap-4 mb-4">
                <a href="#" class="text-secondary hover-lift"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="text-secondary hover-lift"><i class="bi bi-linkedin"></i></a>
                <a href="#" class="text-secondary hover-lift"><i class="bi bi-github"></i></a>
            </div>
            <p class="text-muted text-small mb-0">&copy; {{ date('Y') }} ParkEase. Sustainable Mobility.</p>
        </div>
    </footer>

    <!-- Floating Active Session Widget -->
    @auth
        @php
            $activeBookingWidget = \App\Models\Booking::where('user_id', auth()->user()->_id)
                ->where('status', 'confirmed')
                ->where('date', date('Y-m-d'))
                ->with(['parkingLot:id,name,latitude,longitude', 'slot:id,slot_number'])
                ->get()
                ->filter(function($b) {
                    $times = explode('-', $b->time_slot_id);
                    $start = \Carbon\Carbon::parse($b->date . ' ' . $times[0]);
                    $end = \Carbon\Carbon::parse($b->date . ' ' . $times[1]);
                    return now()->between($start, $end);
                })
                ->first();
        @endphp

        @if($activeBookingWidget)
            @php
                $endTimeStrWidget = explode('-', $activeBookingWidget->time_slot_id)[1];
                $endWidget = \Carbon\Carbon::parse($activeBookingWidget->date . ' ' . $endTimeStrWidget);
            @endphp
            <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050;">
                <div class="surface-glass p-4 hover-lift" style="width: 320px;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--brand-aqua); animation: pulse 2s infinite;"></div>
                        <span class="text-h6 mb-0">Active Session</span>
                    </div>
                    <h5 class="fw-bold mb-3 text-truncate">{{ $activeBookingWidget->parkingLot->name ?? 'Parking' }}</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="text-small">Slot <span class="fw-bold text-primary">{{ $activeBookingWidget->slot->slot_number ?? '?' }}</span></div>
                        <span class="fw-bold live-timer" data-endtime="{{ $endWidget->toIso8601String() }}" style="font-family: 'Outfit', monospace; font-size: 1.1rem; color: var(--brand-aqua);">--:--:--</span>
                    </div>
                    
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $activeBookingWidget->parkingLot->latitude }},{{ $activeBookingWidget->parkingLot->longitude }}" target="_blank" class="btn btn-secondary w-100">
                        <i class="bi bi-cursor"></i> Directions
                    </a>
                </div>
            </div>
        @endif
    @endauth

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script data-clerk-publishable-key="{{ env('VITE_CLERK_PUBLISHABLE_KEY') }}" src="{{ env('CLERK_JS_URL') }}" type="text/javascript"></script>

    <script>
        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        }

        document.addEventListener("DOMContentLoaded", async function () {
            await Clerk.load();
            const authContainer = document.getElementById('nav-auth-container');

            if (Clerk.user) {
                @auth
                    const currentRole = '{{ auth()->user()->role }}';
                    const kycStatus = '{{ auth()->user()->kyc_status }}';
                    const hasOnboarded = '{{ auth()->user()->onboarding_completed }}' === '1' || '{{ auth()->user()->onboarding_completed }}' === 'true';
                    
                    if (hasOnboarded) {
                        // Role Switcher Logic - ONLY for Verified Hosts
                        if (kycStatus === 'verified') {
                            const switcher = document.createElement('div');
                            switcher.className = 'nav-pills-premium d-none d-lg-flex me-2';
                            switcher.innerHTML = `
                                <a href="/switch-role" class="nav-link ${currentRole === 'user' ? 'active' : ''}">User</a>
                                <a href="/switch-role" class="nav-link ${currentRole === 'owner' ? 'active' : ''}">Host</a>
                            `;
                            authContainer.appendChild(switcher);
                        }

                        // Profile Dropdown Setup
                        const profile = document.createElement('div');
                        profile.className = 'dropdown';
                        
                        // Construct the dropdown based on host status
                        let hostDropdownOption = '';
                        if (kycStatus === 'verified') {
                            hostDropdownOption = `<a class="dropdown-item-premium" href="/owner/dashboard"><i class="bi bi-buildings"></i> Host Dashboard</a>`;
                        } else {
                            hostDropdownOption = `<a class="dropdown-item-premium" href="/switch-role"><i class="bi bi-shop"></i> Become a Host</a>`;
                        }

                        profile.innerHTML = `
                            <img src="${Clerk.user.imageUrl}" class="rounded-circle border border-2 hover-lift" style="width: 44px; height: 44px; cursor:pointer; border-color: var(--border-default);" data-bs-toggle="dropdown">
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-premium">
                                <div class="px-3 py-3 d-flex align-items-center gap-3">
                                    <img src="${Clerk.user.imageUrl}" class="rounded-circle" style="width: 40px; height: 40px;">
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-truncate" style="font-size: 1rem; color: var(--text-primary); line-height: 1.2;">${Clerk.user.fullName || 'User'}</div>
                                        <div class="text-truncate text-small" style="color: var(--text-muted);">${Clerk.user.primaryEmailAddress.emailAddress}</div>
                                    </div>
                                </div>
                                <div class="dropdown-divider-premium"></div>
                                <div class="px-2 py-1">
                                    <div class="text-h6 px-3 py-2">General</div>
                                    <a class="dropdown-item-premium" href="/dashboard"><i class="bi bi-grid"></i> Dashboard</a>
                                    <a class="dropdown-item-premium" href="/dashboard?tab=transactions"><i class="bi bi-receipt"></i> Transactions</a>
                                </div>
                                <div class="dropdown-divider-premium"></div>
                                <div class="px-2 py-1">
                                    <div class="text-h6 px-3 py-2">Hosting</div>
                                    ${hostDropdownOption}
                                </div>
                                <div class="dropdown-divider-premium"></div>
                                <div class="px-2 py-1">
                                    <a class="dropdown-item-premium" href="/settings"><i class="bi bi-gear"></i> Settings</a>
                                    <a class="dropdown-item-premium text-danger mt-1" href="javascript:void(0)" onclick="handleGlobalLogout()"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
                                </div>
                            </div>
                        `;
                        authContainer.appendChild(profile);
                    }
                @endauth

                // Sync Logic
                if (!sessionStorage.getItem('clerk_synced')) {
                    const token = await Clerk.session.getToken();
                    try {
                        const response = await fetch('/api/auth/clerk-sync', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                clerk_id: Clerk.user.id,
                                email: Clerk.user.primaryEmailAddress.emailAddress,
                                first_name: Clerk.user.firstName,
                                last_name: Clerk.user.lastName
                            })
                        });
                        if (response.ok) {
                            sessionStorage.setItem('clerk_synced', 'true');
                            window.location.reload();
                        }
                    } catch (e) { console.error(e); }
                }
            } else {
                authContainer.innerHTML = `
                    <a href="/login" class="nav-link fw-bold px-0 me-3">Log in</a>
                    <a href="/register" class="btn btn-brand">Sign up</a>
                `;
            }
            
            if (typeof window.renderClerkComponent === 'function') {
                window.renderClerkComponent();
            }

            setTimeout(() => {
                const splash = document.getElementById('splash-screen');
                if (splash) {
                    splash.style.opacity = '0';
                    setTimeout(() => splash.style.display = 'none', 400);
                }
            }, 400);

            // Timer logic
            if (document.querySelector('.live-timer')) {
                setInterval(() => {
                    document.querySelectorAll('.live-timer').forEach(el => {
                        const endTime = new Date(el.getAttribute('data-endtime')).getTime();
                        const now = new Date().getTime();
                        const distance = endTime - now;
                        if (distance < 0) { el.innerHTML = "EXPIRED"; return; }
                        const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const s = Math.floor((distance % (1000 * 60)) / 1000);
                        el.innerHTML = (h < 10 ? "0"+h : h) + "h " + (m < 10 ? "0"+m : m) + "m " + (s < 10 ? "0"+s : s) + "s";
                    });
                }, 1000);
            }
        });

        window.handleGlobalLogout = async function() {
            await Clerk.signOut();
            fetch('/api/logout', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).finally(() => window.location.href = '/');
        }
    </script>
    @stack('scripts')
</body>
</html>
