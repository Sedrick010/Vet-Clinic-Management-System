@extends('layouts.app')

@section('title', 'Admin - Subscription Management')

@section('page_name', 'Subscription Management')

@section('styles')
<style>
    .status-badge {
        width: 12px;
        height: 12px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }
    .active-status {
        background-color: #48c78e;
    }
    .inactive-status {
        background-color: #f14668;
    }
    .expires-soon {
        background-color: #f39f5a;
    }
    .subscription-card {
        transition: all 0.3s ease;
    }
    .subscription-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    /* Plan color indicators */
    .plan-color-indicator {
        width: 14px;
        height: 14px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }
    .plan-free {
        background-color: #6c757d;
    }
    .plan-basic {
        background-color: #17a2b8;
    }
    .plan-standard {
        background-color: #007bff;
    }
    .plan-business {
        background-color: #6f42c1;
    }
    .plan-premium {
        background-color: #9c27b0;
    }
    
    .subscription-row {
        transition: all 0.2s ease;
    }
    .subscription-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .btn-group .btn {
        position: relative;
        z-index: 1;
    }
    .btn-group form {
        margin: 0;
        padding: 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg mb-4">
                <div class="card-header pb-0 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Clinic Subscriptions</h5>
                            <p class="text-sm mb-0">Manage subscription plans for all clinics</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.subscription-requests.index') }}" class="btn btn-sm btn-info me-2">
                                <i class="fas fa-credit-card me-1"></i> Subscription Requests
                            </a>
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item filter-item" href="#" data-filter="all">All Subscriptions</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="active">Active Only</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="inactive">Inactive Only</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="expires-soon">Expiring Soon</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="free">Free Plan</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="basic">Basic Plan</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="standard">Standard Plan</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="business">Business Plan</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="premium">Premium Plan</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Expiration</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Created</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clinics as $clinic)
                                @php
                                    $expirationDate = $clinic->subscription_ends_at;
                                    $expiresInDays = $expirationDate ? now()->diffInDays($expirationDate, false) : null;
                                    $isExpiringSoon = $expiresInDays !== null && $expiresInDays > 0 && $expiresInDays <= 7;
                                    
                                    $filterClasses = [];
                                    $filterClasses[] = $clinic->is_subscription_active ? 'active' : 'inactive';
                                    $filterClasses[] = $clinic->subscription_plan;
                                    if ($isExpiringSoon) $filterClasses[] = 'expires-soon';
                                @endphp
                                <tr class="subscription-row {{ implode(' ', $filterClasses) }}">
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div>
                                                <div class="status-badge {{ $clinic->is_active ? 'active-status' : 'inactive-status' }}"></div>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $clinic->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $clinic->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="px-3 py-1">
                                            <div class="d-flex align-items-center">
                                                <div class="plan-color-indicator plan-{{ $clinic->subscription_plan }}"></div>
                                                <span class="ms-2 fw-bold">
                                                    {{ $subscriptionPlans[$clinic->subscription_plan] ?? ucfirst($clinic->subscription_plan) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="px-3 py-1">
                                            <span class="badge bg-{{ $clinic->is_subscription_active ? 'success' : 'danger' }}">
                                                {{ $clinic->is_subscription_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="px-3 py-1">
                                            @if($expirationDate)
                                                <span class="{{ $isExpiringSoon ? 'text-warning fw-bold' : '' }}">
                                                    {{ $expirationDate->format('M d, Y') }}
                                                    @if($isExpiringSoon)
                                                        <i class="fas fa-exclamation-circle ms-1" data-bs-toggle="tooltip" title="Expiring soon"></i>
                                                    @endif
                                                </span>
                                                <p class="text-xs text-secondary mb-0">
                                                    @if($expiresInDays !== null)
                                                        @if($expiresInDays > 0)
                                                            {{ $expiresInDays }} day(s) left
                                                        @elseif($expiresInDays === 0)
                                                            Expires today
                                                        @else
                                                            Expired {{ abs($expiresInDays) }} day(s) ago
                                                        @endif
                                                    @endif
                                                </p>
                                            @else
                                                <span class="text-muted">No expiration</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="px-3 py-1">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                {{ $clinic->created_at->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="px-3 py-1">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.clinics.subscription.edit', $clinic->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Subscription">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.clinics.subscription.change.form', $clinic->id) }}" class="btn btn-sm btn-info ms-1" data-bs-toggle="tooltip" title="Change & Extend">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                                <form action="{{ route('admin.clinics.subscription.toggle', $clinic->id) }}" method="POST" style="display:inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $clinic->is_subscription_active ? 'btn-danger' : 'btn-success' }} ms-1" data-bs-toggle="tooltip" title="{{ $clinic->is_subscription_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas {{ $clinic->is_subscription_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.clinics.show', $clinic->id) }}" class="btn btn-sm btn-secondary ms-1" data-bs-toggle="tooltip" title="View Clinic">
                                                    <i class="fas fa-hospital"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips with Bootstrap 5 syntax
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: document.body
            });
        });
        
        // Filtering functionality
        const filterItems = document.querySelectorAll('.filter-item');
        const subscriptionRows = document.querySelectorAll('.subscription-row');
        
        filterItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active filter in dropdown
                filterItems.forEach(fi => fi.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                // Show all rows if filter is 'all'
                if (filter === 'all') {
                    subscriptionRows.forEach(row => {
                        row.style.display = '';
                    });
                    return;
                }
                
                // Apply filter
                subscriptionRows.forEach(row => {
                    if (row.classList.contains(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Initialize the "All" filter by default
        document.querySelector('.filter-item[data-filter="all"]')?.classList.add('active');
        
        // Add loading state to buttons and forms
        document.querySelectorAll('.btn-group a, .btn-group button').forEach(button => {
            button.addEventListener('click', function() {
                // Skip if inside a form (those will be handled separately)
                if (this.closest('form') && this.tagName !== 'FORM') return;
                
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                
                // Store original class and add spinner
                this.setAttribute('data-original-icon', originalClass);
                icon.className = 'fas fa-spinner fa-spin';
                
                // Disable button to prevent double-clicks
                this.classList.add('disabled');
            });
        });
        
        // Handle form submissions with loading state
        document.querySelectorAll('.btn-group form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button');
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                
                // Store original class and add spinner
                button.setAttribute('data-original-icon', originalClass);
                icon.className = 'fas fa-spinner fa-spin';
                
                // Disable button to prevent double-clicks
                button.classList.add('disabled');
            });
        });
    });
</script>
@endpush 