@extends('layouts.app')

@section('title', 'Subscription Limit Reached')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Subscription Limit Reached</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/limit-reached.svg') }}" alt="Limit Reached" class="img-fluid" style="max-height: 200px;">
                    </div>
                    
                    <h4 class="text-center mb-3">You've reached your {{ $limitType ?? 'subscription' }} limit!</h4>
                    
                    <p class="lead text-center mb-4">
                        Your current {{ $clinic->subscription_plan ?? 'free' }} plan allows for 
                        <strong>{{ $limit ?? 'limited' }}</strong> {{ $limitType ?? 'items' }}.
                    </p>
                    
                    <div class="card bg-primary bg-gradient text-white shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>Why not upgrade?</h5>
                            <p class="text-white">Upgrade your subscription plan to increase your limits and access more features!</p>
                            <hr class="border-light">
                            <p class="mb-0 text-white">
                                Your current plan: <strong>{{ ucfirst($clinic->subscription_plan ?? 'free') }}</strong><br>
                                @if(($clinic->subscription_plan ?? 'free') == 'free')
                                    Upgrade to <strong>Basic</strong> for more {{ $limitType ?? 'items' }} 
                                    or <strong>Premium</strong> for unlimited {{ $limitType ?? 'items' }}.
                                @elseif(($clinic->subscription_plan ?? 'free') == 'basic')
                                    Upgrade to <strong>Premium</strong> for unlimited {{ $limitType ?? 'items' }} 
                                    and many more features.
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('subscription.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-crown me-2"></i>View Subscription Plans
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg ms-2">
                            <i class="fas fa-home me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 