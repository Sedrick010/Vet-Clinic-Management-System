@extends('layouts.app')

@section('title', 'Clinic Details')
@section('page_name', 'Clinic Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ $clinic->name }}</h6>
                            <p class="text-sm mb-0">Clinic Details and Information</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.clinics.subscription.edit', $clinic) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-cog me-1"></i> Manage Subscription
                            </a>
                            <a href="{{ route('admin.clinics.index') }}" class="btn btn-sm btn-secondary ms-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Clinics
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Clinic Information</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-xs text-secondary" style="width: 150px;">Clinic Name</th>
                                            <td class="font-weight-bold">{{ $clinic->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Subdomain</th>
                                            <td>{{ $clinic->subdomain }}.{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Email</th>
                                            <td>{{ $clinic->email }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Phone</th>
                                            <td>{{ $clinic->phone ?? 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Address</th>
                                            <td>{{ $clinic->address ?? 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Database Name</th>
                                            <td><code>{{ $clinic->database_name }}</code></td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Status</th>
                                            <td>
                                                <span class="badge {{ $clinic->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                    {{ $clinic->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                
                                                <form action="{{ route('admin.clinics.toggle-active', $clinic) }}" method="POST" class="d-inline ms-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-xs btn-{{ $clinic->is_active ? 'danger' : 'success' }}">
                                                        {{ $clinic->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Enabled</th>
                                            <td>
                                                <span class="badge {{ $clinic->is_enabled ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                    {{ $clinic->is_enabled ? 'Enabled' : 'Disabled' }}
                                                </span>
                                                
                                                @if($clinic->is_enabled)
                                                <button type="button" class="btn btn-xs btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#disableClinicModal">
                                                    Disable
                                                </button>
                                                @else
                                                <form action="{{ route('admin.clinics.toggle-enabled', $clinic) }}" method="POST" class="d-inline ms-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-xs btn-success">
                                                        Enable
                                                    </button>
                                                </form>
                                                @endif
                                                
                                                @if(!$clinic->is_enabled && $clinic->disable_reason)
                                                <div class="mt-2">
                                                    <small class="text-danger"><strong>Reason:</strong> {{ $clinic->disable_reason }}</small>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Subscription Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-xs text-secondary" style="width: 150px;">Plan</th>
                                            <td>
                                                <span class="badge {{ $clinic->subscription_plan === 'premium' ? 'bg-gradient-primary' : ($clinic->subscription_plan === 'basic' ? 'bg-gradient-info' : 'bg-gradient-secondary') }}">
                                                    {{ ucfirst($clinic->subscription_plan ?? 'Free') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Status</th>
                                            <td>
                                                <span class="badge {{ $clinic->is_subscription_active ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                    {{ $clinic->is_subscription_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Expires</th>
                                            <td>
                                                @if($clinic->subscription_ends_at)
                                                    {{ $clinic->subscription_ends_at->format('M d, Y') }}
                                                    <small class="text-muted">
                                                        ({{ $clinic->subscription_ends_at->isPast() ? 'Expired' : $clinic->subscription_ends_at->diffForHumans() }})
                                                    </small>
                                                @else
                                                    <span class="text-muted">No expiration date</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!$clinic->is_subscription_active && $clinic->deactivation_reason)
                                        <tr>
                                            <th class="text-xs text-secondary">Deactivation Reason</th>
                                            <td>{{ $clinic->deactivation_reason }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Registration Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th class="text-xs text-secondary" style="width: 150px;">Owner Name</th>
                                            <td>{{ $clinic->owner_name ?? 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Owner Email</th>
                                            <td>{{ $clinic->owner_email ?? 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Registered On</th>
                                            <td>{{ $clinic->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-xs text-secondary">Last Updated</th>
                                            <td>{{ $clinic->updated_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.clinics.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Clinics
                </a>
                <div>
                    <a href="{{ route('admin.clinics.subscription.edit', $clinic) }}" class="btn btn-primary">
                        <i class="fas fa-cog me-1"></i> Manage Subscription
                    </a>
                    <form action="{{ route('admin.clinics.destroy', $clinic->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Are you sure you want to delete this clinic? This will permanently remove all their data and cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete Clinic
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable Clinic Modal -->
<div class="modal fade" id="disableClinicModal" tabindex="-1" role="dialog" aria-labelledby="disableClinicModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.clinics.toggle-enabled', $clinic) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title" id="disableClinicModalLabel">Disable Clinic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p>Disabling this clinic will prevent access to the clinic site and its subdomain. This action can be reversed later.</p>
                        <label for="disable_reason" class="form-label">Reason for disabling</label>
                        <textarea class="form-control" id="disable_reason" name="disable_reason" rows="3" required></textarea>
                        <small class="text-muted">This reason will be displayed to users when they try to access the clinic.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disable Clinic</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 