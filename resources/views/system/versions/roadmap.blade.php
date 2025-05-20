@extends('layouts.app')

@section('title', 'Version Roadmap & Future Features')
@section('page_name', 'Version Roadmap & Future Features')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 bg-gradient-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h5 class="mb-0 text-white">Version Roadmap <span class="badge bg-info ms-2">v{{ $currentVersion }}</span></h5>
                            <p class="text-sm mb-0 opacity-8">Explore upcoming features and enhancements</p>
                            
                            <!-- VERSION INDICATOR - This will change with each release to verify updates -->
                            <div class="mt-2 bg-gradient-primary px-3 py-1 rounded d-inline-block">
                                <span class="badge bg-white text-primary">VERSION IDENTIFIER: ROADMAP-UI-v4.1.2</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('version.manage') }}" class="btn btn-sm btn-white me-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Versions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="bg-gradient-info text-white rounded p-4">
                                <h6 class="text-white mb-3">Currently Running Version v{{ $currentVersion }}</h6>
                                <p class="text-sm mb-0">This roadmap shows planned features for upcoming releases. The development team is actively working on these enhancements to improve your experience.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline timeline-one-side mb-4" data-timeline-axis-style="dashed">
                        @foreach($upcomingFeatures as $index => $feature)
                            <div class="timeline-block">
                                <span class="timeline-step bg-{{ $feature['status'] == 'coming-soon' ? 'success' : ($feature['status'] == 'in-development' ? 'warning' : 'info') }}">
                                    <i class="fas {{ $feature['status'] == 'coming-soon' ? 'fa-rocket' : ($feature['status'] == 'in-development' ? 'fa-code' : 'fa-lightbulb') }}"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark">
                                        {{ $feature['title'] }}
                                        @if($feature['status'] == 'coming-soon')
                                            <span class="badge bg-gradient-success">Coming Soon</span>
                                        @elseif($feature['status'] == 'in-development')
                                            <span class="badge bg-gradient-warning">In Development</span>
                                        @else
                                            <span class="badge bg-gradient-info">Planned</span>
                                        @endif
                                    </h6>
                                    <p class="text-secondary text-sm">Expected in {{ $feature['estimated_version'] }}</p>
                                    <p class="text-sm">{{ $feature['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0">How to Provide Feedback</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-sm mb-3">We value your input on which features should be prioritized. Please use one of the following methods to share your feedback:</p>
                                    <ul class="mb-0">
                                        <li class="text-sm mb-2">Submit a feature request through the support portal</li>
                                        <li class="text-sm mb-2">Contact your account manager to discuss specific needs</li>
                                        <li class="text-sm">Participate in our quarterly user feedback sessions</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        margin: 0 0 2rem;
        padding: 0;
        list-style: none;
    }
    
    .timeline-one-side:before {
        left: 1rem;
        background-color: #e9ecef;
        height: 100%;
        width: 2px;
        content: "";
        position: absolute;
        top: 0;
    }
    
    .timeline-block {
        position: relative;
        margin-bottom: 1.5rem;
        display: flex;
    }
    
    .timeline-step {
        width: 2.7rem;
        height: 2.7rem;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .timeline-step i {
        font-size: 1.1rem;
    }
    
    .timeline-content {
        width: calc(100% - 3.7rem);
        padding: 0.5rem 0;
    }
</style>
@endpush 