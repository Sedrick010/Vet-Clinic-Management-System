<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main" style="background-color: var(--card-color); box-shadow: {{ $theme['name'] == 'dark' ? '0 20px 27px 0 rgba(0,0,0,0.3)' : '0 20px 27px 0 rgba(0,0,0,0.05)' }};">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            @if(session('current_clinic_id') && isset($clinic))
                <img src="{{ $clinic->getLogoUrl() }}" class="navbar-brand-img h-100" alt="{{ $clinic->name }} logo" style="max-height: 40px; object-fit: contain;">
                <span class="ms-1 font-weight-bold" style="color: var(--text-color);">{{ $clinic->name }}</span>
            @else
                <img src="{{ asset('favicon.ico') }}" class="navbar-brand-img h-100" alt="main_logo">
                <span class="ms-1 font-weight-bold" style="color: var(--text-color);">Vet Clinic System</span>
            @endif
        </a>
    </div>
    
    <hr class="horizontal {{ $theme['name'] == 'dark' ? 'light opacity-2' : 'dark' }} mt-0">
    
    <style>
    /* Custom styling for sidebar to maintain consistency */
    .sidenav .nav-link {
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        margin: 0.2rem 1rem;
        color: var(--text-color);
    }
    
    .sidenav .nav-link:hover {
        background-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(94, 114, 228, 0.1)' }};
    }
    
    .sidenav .nav-link.active {
        background-color: {{ $theme['colors']['primary'] ?? '#5e72e4' }};
        background-image: linear-gradient(310deg, {{ $theme['colors']['primary'] ?? '#5e72e4' }} 0%, {{ $theme['colors']['secondary'] ?? '#825ee4' }} 100%);
        box-shadow: 0 5px 15px {{ $theme['name'] == 'dark' ? 'rgba(0, 0, 0, 0.5)' : 'rgba(94, 114, 228, 0.3)' }};
    }
    
    .sidenav .nav-link.active .icon-shape {
        background-color: #fff;
    }
    
    .sidenav .nav-link.active .icon-shape i {
        color: {{ $theme['colors']['primary'] ?? '#5e72e4' }} !important;
    }
    
    .sidenav .nav-link.active .nav-link-text {
        color: #fff !important;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    .icon-shape {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background-color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.2)' : '#fff' }};
        transition: all 0.3s ease;
        box-shadow: {{ $theme['name'] == 'dark' ? '0 2px 5px rgba(0, 0, 0, 0.3)' : '0 2px 5px rgba(0, 0, 0, 0.1)' }};
    }
    
    .sidenav .nav-link:not(.active) .icon-shape i {
        color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.95)' : 'inherit' }} !important;
    }
    
    /* Custom colors for icons */
    .text-primary, .text-info, .text-success, .text-warning, .text-danger, .text-dark, .text-purple {
        color: {{ $theme['name'] == 'dark' ? '#ffffff' : 'inherit' }} !important;
    }
    
    .text-purple {
        color: {{ $theme['name'] == 'dark' ? '#a78bfa' : '#8b5cf6' }} !important;
    }
    
    /* Admin dashboard icon */
    .admin-icon {
        background: {{ $theme['name'] == 'dark' ? 'rgba(225, 78, 202, 0.2)' : 'linear-gradient(310deg, #e14eca 0%, #ba54f5 100%)' }};
        color: {{ $theme['name'] == 'dark' ? '#e14eca' : 'white' }} !important;
    }
    
    .nav-link.active .admin-icon {
        background: white;
        color: #e14eca !important;
    }
    
    /* Clinic management icon */
    .clinics-icon {
        background: {{ $theme['name'] == 'dark' ? 'rgba(45, 206, 137, 0.2)' : 'linear-gradient(310deg, #2dce89 0%, #2dcca8 100%)' }};
        color: {{ $theme['name'] == 'dark' ? '#2dce89' : 'white' }} !important;
    }
    
    .nav-link.active .clinics-icon {
        background: white;
        color: #2dce89 !important;
    }
    
    .nav-item h6.text-uppercase {
        margin-left: 1rem;
        font-size: 0.65rem;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        color: {{ $theme['name'] == 'dark' ? 'rgba(255, 255, 255, 0.6)' : '#8898aa' }};
        font-weight: 700;
        letter-spacing: 0.03em;
    }
    </style>
    
    <div class="collapse navbar-collapse w-auto max-height-vh-100 h-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Only show main dashboard for non-admin users -->
            @if(!Auth::check() || Auth::user()->role !== 'admin')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-tachometer-alt text-primary"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            @endif
            
            <!-- Admin Section -->
            @if(Auth::check() && Auth::user()->role === 'admin')
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Admin</h6>
            </li>
            
            @foreach($adminMenu ?? [] as $menuItem)
            <li class="nav-item">
                <a class="nav-link {{ Request::is($menuItem['matches'][0]) ? 'active' : '' }}" href="{{ route($menuItem['route']) }}">
                    @php
                        $specialClass = '';
                        if ($menuItem['route'] === 'admin.dashboard') {
                            $specialClass = 'admin-icon';
                        } elseif ($menuItem['route'] === 'admin.clinics.index') {
                            $specialClass = 'clinics-icon';
                        }
                    @endphp
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center {{ $specialClass }}">
                        <i class="{{ $menuItem['icon'] }} {{ $specialClass ? '' : 'text-'.$menuItem['color'] }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">{{ $menuItem['name'] }}</span>
                </a>
            </li>
            @endforeach
            @endif
            
            <!-- Clinic Functionality - Only show for non-admin users or clinic owners -->
            @if(!Auth::check() || Auth::user()->role !== 'admin')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('appointments*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-calendar-alt text-success"></i>
                    </div>
                    <span class="nav-link-text ms-1">Appointments</span>
                </a>
            </li>
            
            <!-- Clients Management -->
            <li class="nav-item">
                <a class="nav-link {{ Request::is('clients*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <span class="nav-link-text ms-1">Clients</span>
                </a>
            </li>
            
            <!-- Pets Management -->
            <li class="nav-item">
                <a class="nav-link {{ Request::is('pets*') ? 'active' : '' }}" href="{{ route('pets.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-paw text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pets</span>
                </a>
            </li>
            
            <!-- Inventory Management - Only visible to clinic staff with active session -->
            @if(session('tenant_user') && session('current_clinic_id'))
            <li class="nav-item">
                <a class="nav-link {{ Request::is('inventory') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-boxes text-info"></i>
                    </div>
                    <span class="nav-link-text ms-1">Inventory</span>
                </a>
            </li>
            @endif
            
            <!-- Staff Management - Only visible to clinic owners -->
            @if(session('current_clinic_id') && Auth::check() && Auth::user()->role === 'owner')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('staff*') ? 'active' : '' }}" href="{{ route('staff.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-tie text-purple"></i>
                    </div>
                    <span class="nav-link-text ms-1">Staff Management</span>
                </a>
            </li>
            @endif
            
            <!-- Clinic Information -->
            @if(session('current_clinic_id'))
            <li class="nav-item">
                <a class="nav-link {{ Request::is('clinic-info') ? 'active' : '' }}" href="{{ route('clinic.info') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-hospital text-info"></i>
                    </div>
                    <span class="nav-link-text ms-1">Clinic Info</span>
                </a>
            </li>
            
            <!-- System Updates -->
            <li class="nav-item">
                <a class="nav-link {{ Request::is('system/updates*') ? 'active' : '' }}" href="{{ route('system.updates.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sync-alt text-success"></i>
                    </div>
                    <span class="nav-link-text ms-1">System Updates</span>
                    @php
                        $systemUpdateService = app(\App\Services\SystemUpdateService::class);
                        $updateCheck = $systemUpdateService->checkForUpdates($clinic);
                        $hasUpdates = isset($updateCheck['success']) && $updateCheck['success'] && $updateCheck['has_updates'];
                    @endphp
                    @if($hasUpdates)
                        <span class="badge bg-gradient-danger text-white ms-auto">{{ count($updateCheck['updates']) }}</span>
                    @endif
                </a>
            </li>
            @endif
            
            <!-- Support Tickets - For tenants and regular users -->
            @if(!Auth::check() || Auth::user()->role !== 'admin')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('support*') ? 'active' : '' }}" href="{{ route('support.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-ticket-alt {{ Request::is('support*') ? '' : 'text-warning' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">Support</span>
                </a>
            </li>
            
            <!-- Subscription Management - For tenants and regular users -->
            <li class="nav-item">
                <a class="nav-link {{ Request::is('subscription*') ? 'active' : '' }}" href="{{ route('subscription.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-gem text-warning"></i>
                    </div>
                    <span class="nav-link-text ms-1">Subscription</span>
                </a>
            </li>
            @endif
            
            <!-- Premium Reports - Only visible if clinic has active subscription -->
            @if(isset($clinic) && $clinic->is_subscription_active)
            <li class="nav-item mt-3" id="premium-features-section">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Premium Features</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('premium*') ? 'active' : '' }}" href="{{ route('premium.reports') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-bar text-warning"></i>
                    </div>
                    <span class="nav-link-text ms-1">Premium Reports</span>
                    <span class="badge bg-gradient-warning text-white ms-auto">PRO</span>
                </a>
            </li>
            @endif
            @endif
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
            </li>
            
            <!-- Profile link -->
            <li class="nav-item">
                @if(Auth::check())
                <a class="nav-link {{ Request::is('profile') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-circle {{ $theme['name'] == 'dark' ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">My Profile</span>
                </a>
                @elseif(session()->has('tenant_user'))
                <a class="nav-link {{ Request::is('tenant/profile') ? 'active' : '' }}" href="{{ route('tenant.profile.edit') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-circle {{ $theme['name'] == 'dark' ? 'text-white' : 'text-dark' }}"></i>
                    </div>
                    <span class="nav-link-text ms-1">My Profile</span>
                </a>
                @endif
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="icon icon-shape icon-sm shadow border-radius-md {{ $theme['name'] == 'dark' ? 'bg-dark' : 'bg-white' }} text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
            
            @if(isset($clinic) && (!Auth::check() || Auth::user()->role !== 'admin'))
            <li class="nav-item mt-4">
                <hr class="horizontal dark">
                <div class="px-3 py-2">
                    <p class="text-xs mb-1">Subscription Status:</p>
                    <div class="d-flex align-items-center">
                        <span id="subscription-status-badge" class="badge {{ $clinic->is_subscription_active ? 'bg-gradient-success' : 'bg-gradient-danger' }} me-2">
                            {{ $clinic->is_subscription_active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                        <a href="{{ request()->url() }}?refresh={{ time() }}" class="btn btn-sm btn-outline-primary" title="Refresh to see latest features">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </a>
                    </div>
                </div>
            </li>
            @endif
        </ul>
    </div>
</aside> 