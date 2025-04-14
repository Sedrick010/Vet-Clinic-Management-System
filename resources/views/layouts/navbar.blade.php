<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">@yield('page_name', 'Dashboard')</li>
            </ol>
            <h6 class="font-weight-bolder mb-0">@yield('page_name', 'Dashboard')</h6>
        </nav>
        
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" placeholder="Type here...">
                </div>
            </div>
            
            <ul class="navbar-nav justify-content-end">
                <!-- Admin Links -->
                @if(Auth::check() && Auth::user()->role === 'admin')
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="dropdownAdminMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-shield me-sm-1"></i>
                        <span class="d-sm-inline d-none">
                            Admin
                            <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownAdminMenu">
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('admin.dashboard') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-tachometer-alt text-primary"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Admin Dashboard
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('admin.clinics.index') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-clinic-medical text-success"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Clinic Approvals
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                <!-- Clinic Selector -->
                @if(Auth::check())
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="dropdownClinicSelector" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clinic-medical me-sm-1"></i>
                        <span class="d-sm-inline d-none">
                            @php
                                $currentClinicId = session('current_clinic_id', Auth::user()->clinic_id);
                                $clinic = \App\Models\Clinic::find($currentClinicId);
                            @endphp
                            {{ $clinic ? $clinic->name : 'Select Clinic' }}
                            <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownClinicSelector">
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('clinics.select') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-exchange-alt text-primary"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Switch Clinic
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('clinics.create') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-plus-circle text-success"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Register New Clinic
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                <!-- Quick Links - Only shown for tenant users -->
                @if(session('tenant_user') || (Auth::check() && Auth::user()->role !== 'admin'))
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="dropdownQuickLinks" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th me-sm-1"></i>
                        <span class="d-sm-inline d-none">
                            Quick Links
                            <i class="fas fa-chevron-down ms-1 text-xs"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownQuickLinks">
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('staff.index') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Staff Management
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="#">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-calendar-alt text-success"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Appointments
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="#">
                                <div class="d-flex py-1">
                                    <div class="my-auto me-3">
                                        <i class="fas fa-paw text-info"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Patients
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                <li class="nav-item d-flex align-items-center">
                    <a href="{{ route('profile.edit') }}" class="nav-link text-body font-weight-bold px-0">
                        <i class="fa fa-user me-sm-1"></i>
                        <span class="d-sm-inline d-none">{{ Auth::user()->name ?? 'Profile' }}</span>
                    </a>
                </li>
                
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
                
                <li class="nav-item px-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0">
                        <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                    </a>
                </li>
                
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <i class="fas fa-bell avatar avatar-sm bg-gradient-dark me-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">New notification</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            Just now
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <img src="{{ asset('assets/img/small-logos/logo-spotify.svg') }}" class="avatar avatar-sm bg-gradient-dark me-3">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">Medication reminder</span> for Rex
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            1 day ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        
                        <li>
                            <a class="dropdown-item border-radius-md" href="javascript:;">
                                <div class="d-flex py-1">
                                    <div class="avatar avatar-sm bg-gradient-secondary me-3 my-auto">
                                        <i class="fa fa-calendar-check"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            Follow-up appointment scheduled
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            2 days ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Logout -->
                <li class="nav-item d-flex align-items-center">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link text-body font-weight-bold px-0">
                        <i class="fa fa-sign-out-alt me-sm-1"></i>
                        <span class="d-sm-inline d-none">Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav> 