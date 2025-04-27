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
                <!-- User Info Section -->
                @if(Auth::check())
                <div class="d-flex align-items-center">
                    @if(Auth::user()->role !== 'admin')
                    <!-- Current Clinic -->
                    @php
                        $currentClinicId = session('current_clinic_id', Auth::user()->clinic_id);
                        $clinic = \App\Models\Clinic::find($currentClinicId);
                    @endphp
                    @if($clinic)
                    <div class="d-flex flex-column me-4">
                        <p class="mb-0 text-xs text-secondary">Current Clinic</p>
                        <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                    </div>
                    @endif
                    @endif
                    
                    <!-- User Info -->
                    <div class="d-flex flex-column me-3">
                        <h6 class="mb-0 text-sm">{{ Auth::user()->name }}</h6>
                        <p class="mb-0 text-xs text-secondary">
                            @if(Auth::user()->role === 'admin')
                                Administrator
                            @elseif(Auth::user()->role === 'owner')
                                Clinic Owner
                            @elseif(Auth::user()->role === 'veterinarian')
                                Veterinarian
                            @elseif(Auth::user()->role === 'staff')
                                Staff Member
                            @elseif(Auth::user()->role === 'receptionist')
                                Receptionist
                            @else
                                {{ ucfirst(Auth::user()->role) }}
                            @endif
                        </p>
                    </div>
                </div>
                @elseif(session()->has('tenant_user'))
                <div class="d-flex align-items-center">
                    <!-- Current Clinic for Tenant Users -->
                    @php
                        $currentClinicId = session('current_clinic_id');
                        $clinic = \App\Models\Clinic::find($currentClinicId);
                        $tenantUser = (object)session('tenant_user');
                    @endphp
                    @if($clinic)
                    <div class="d-flex flex-column me-4">
                        <p class="mb-0 text-xs text-secondary">Current Clinic</p>
                        <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                    </div>
                    @endif
                    
                    <!-- Tenant User Info -->
                    <div class="d-flex flex-column me-3">
                        <h6 class="mb-0 text-sm">{{ $tenantUser->name }}</h6>
                        <p class="mb-0 text-xs text-secondary">
                            @if($tenantUser->role === 'owner')
                                Clinic Owner
                            @elseif($tenantUser->role === 'veterinarian')
                                Veterinarian
                            @elseif($tenantUser->role === 'staff')
                                Staff Member
                            @elseif($tenantUser->role === 'receptionist')
                                Receptionist
                            @else
                                {{ ucfirst($tenantUser->role) }}
                            @endif
                        </p>
                    </div>
                </div>
                @endif
            </div>
            
            <ul class="navbar-nav justify-content-end">
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
                
                @if(Auth::check())
                <!-- Profile Link -->
                <li class="nav-item d-flex align-items-center ms-2">
                    <a href="{{ route('profile.edit') }}" class="nav-link text-body p-0">
                        <i class="fa fa-user me-sm-1"></i>
                    </a>
                </li>
                
                <!-- Logout -->
                <li class="nav-item d-flex align-items-center ms-2">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link text-body p-0">
                        <i class="fa fa-sign-out-alt"></i>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @elseif(session()->has('tenant_user'))
                <!-- Profile Link -->
                <li class="nav-item d-flex align-items-center ms-2">
                    <a href="{{ route('tenant.profile.edit') }}" class="nav-link text-body p-0">
                        <i class="fa fa-user me-sm-1"></i>
                    </a>
                </li>
                
                <!-- Logout - For tenant users -->
                <li class="nav-item d-flex align-items-center ms-2">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('tenant-logout-form').submit();" class="nav-link text-body p-0">
                        <i class="fa fa-sign-out-alt"></i>
                    </a>
                    <form id="tenant-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav> 