<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.ico') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Vet Clinic System</span>
        </a>
    </div>
    
    <hr class="horizontal dark mt-0">
    
    <div class="collapse navbar-collapse w-auto max-height-vh-100 h-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Dashboard - Only show for non-admin users -->
            @if(!Auth::check() || Auth::user()->role !== 'admin')
            <li class="nav-item">
                <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
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
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-shield text-primary"></i>
                    </div>
                    <span class="nav-link-text ms-1">Admin Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/clinics') ? 'active' : '' }}" href="{{ route('admin.clinics.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-clinic-medical text-success"></i>
                    </div>
                    <span class="nav-link-text ms-1">Clinic Approvals</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/database-check') ? 'active' : '' }}" href="{{ route('admin.database.check') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-database text-info"></i>
                    </div>
                    <span class="nav-link-text ms-1">Database Check</span>
                </a>
            </li>
            @endif
            
            <!-- Clinic Functionality - Only show for non-admin users or specific clinic staff -->
            @if(!Auth::check() || Auth::user()->role !== 'admin') 
            <li class="nav-item">
                <a class="nav-link {{ Request::is('patients*') ? 'active' : '' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-paw text-info"></i>
                    </div>
                    <span class="nav-link-text ms-1">Patients</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('appointments*') ? 'active' : '' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-calendar-alt text-success"></i>
                    </div>
                    <span class="nav-link-text ms-1">Appointments</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('staff*') ? 'active' : '' }}" href="{{ route('staff.index') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-plus text-warning"></i>
                    </div>
                    <span class="nav-link-text ms-1">Add Staff</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('owners*') ? 'active' : '' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pet Owners</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('medications*') ? 'active' : '' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-pills text-primary"></i>
                    </div>
                    <span class="nav-link-text ms-1">Medications</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}" href="#">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-file-invoice-dollar text-info"></i>
                    </div>
                    <span class="nav-link-text ms-1">Invoices</span>
                </a>
            </li>
            @endif
            
            <!-- Account Section -->
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Request::is('profile') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>

            @if(session()->has('tenant_user'))
            <li class="nav-item">
                <a class="nav-link {{ Request::is('theme') ? 'active' : '' }}" href="{{ route('tenant.theme.edit') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-palette text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Theme Settings</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside> 