@extends('layouts.app')

@section('title', 'Create Support Ticket')
@section('page_name', 'Create Support Ticket')

@section('styles')
<style>
    .issue-card {
        transition: transform 0.2s;
    }
    .issue-card:hover {
        transform: translateY(-5px);
    }
    .issue-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="mb-4">
                    <h4 class="mb-1">Contact Support</h4>
                    <p class="text-sm text-secondary mb-0">Our support team is here to help you with any issues or questions you may have.</p>
                </div>

                @if (session('success'))
                    <div class="card bg-gradient-success border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="issue-icon me-3">
                                    <i class="fas fa-check-circle fa-lg text-white"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-0">{{ session('success') }}</h6>
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-gradient-primary border-0 h-100 issue-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="issue-icon me-3">
                                        <i class="fas fa-tools fa-lg text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-1"><strong>Technical Problems</strong></h6>
                                        <p class="text-white mb-0" style="opacity: 0.9">System-related issues and technical support</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-gradient-info border-0 h-100 issue-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="issue-icon me-3">
                                        <i class="fas fa-credit-card fa-lg text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-1"><strong>Billing Support</strong></h6>
                                        <p class="text-white mb-0" style="opacity: 0.9">Subscription and payment inquiries</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-gradient-success border-0 h-100 issue-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="issue-icon me-3">
                                        <i class="fas fa-lightbulb fa-lg text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-1"><strong>Feature Requests</strong></h6>
                                        <p class="text-white mb-0" style="opacity: 0.9">Suggestions for improvements</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-gradient-warning border-0 h-100 issue-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="issue-icon me-3">
                                        <i class="fas fa-key fa-lg text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-1"><strong>Account Access</strong></h6>
                                        <p class="text-white mb-0" style="opacity: 0.9">Login and authentication issues</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-gradient-danger border-0 h-100 issue-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="issue-icon me-3">
                                        <i class="fas fa-database fa-lg text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white mb-1"><strong>Data Management</strong></h6>
                                        <p class="text-white mb-0" style="opacity: 0.9">Questions about your data and records</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger text-white" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('support.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="subject" class="form-control-label">Subject</label>
                        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" 
                            class="form-control" 
                            placeholder="Brief description of your issue"
                            required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="priority" class="form-control-label">Priority</label>
                        <select name="priority" id="priority" class="form-control">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - General question or feedback</option>
                            <option value="medium" {{ old('priority') == 'medium' || old('priority') == null ? 'selected' : '' }}>Medium - Minor issue affecting some functionality</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Significant issue affecting core functionality</option>
                            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical - System unusable or data loss risk</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="message" class="form-control-label">Message</label>
                        <textarea name="message" id="message" rows="6" 
                            class="form-control" 
                            placeholder="Please describe your issue in detail. Include any error messages, steps to reproduce the problem, and what you were trying to do when the issue occurred."
                            required>{{ old('message') }}</textarea>
                        <small class="form-text text-muted">The more details you provide, the faster we can assist you.</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('support.index') }}" class="btn btn-link text-dark px-3 mb-0">
                            <i class="fas fa-arrow-left me-2"></i> Back to tickets
                        </a>
                        <button type="submit" class="btn bg-gradient-primary">
                            <i class="fas fa-paper-plane me-2"></i> Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 