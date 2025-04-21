@extends('layouts.app')

@section('title', 'Subscription Request Submitted')

@section('page-name', 'Subscription Request Submitted')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-check-circle me-1"></i>
                    Thank You for Your Subscription Request
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open-text fa-5x text-success mb-3"></i>
                        <h2>Your Subscription Request Has Been Received</h2>
                        <p class="lead">Thank you for choosing our service!</p>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i> What Happens Next?</h5>
                                <p>Our admin team will review your subscription request. This process typically takes 1-2 business days.</p>
                                <p>Once approved, you will receive a confirmation email with details on how to access your subscription.</p>
                                <p>If additional information is needed, we will contact you using the email address you provided.</p>
                            </div>
                            
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-question-circle me-2"></i> Have Questions?</h5>
                                    <p>If you have any questions about your subscription request, please contact our support team:</p>
                                    <ul>
                                        <li>Email: support@vetclinic.com</li>
                                        <li>Phone: (123) 456-7890</li>
                                        <li>Business Hours: Monday-Friday, 9am-5pm</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('welcome') }}" class="btn btn-primary">
                                    <i class="fas fa-home me-1"></i> Return to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 