@extends('layouts.app')

@section('title', 'Create Support Ticket')
@section('page_name', 'Create Support Ticket')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div class="mb-4">
                    <h4 class="mb-1">Contact Support</h4>
                    <p class="text-sm text-secondary mb-0">Our support team is here to help you with any issues or questions you may have.</p>
                </div>

                <div class="alert alert-info d-flex p-3 mb-4">
                    <div class="icon icon-sm me-3">
                        <i class="fas fa-info-circle opacity-10"></i>
                    </div>
                    <div>
                        <h5 class="text-info mb-1">Common issues you can report:</h5>
                        <ul class="ps-3 mb-0">
                            <li>Technical problems with the system</li>
                            <li>Billing or subscription inquiries</li>
                            <li>Feature requests or suggestions</li>
                            <li>Account access issues</li>
                            <li>Data management questions</li>
                        </ul>
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