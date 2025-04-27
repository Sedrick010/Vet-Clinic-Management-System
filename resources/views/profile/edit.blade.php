@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Profile Information</h6>
                    <p class="text-sm text-secondary mb-0">Update your account's profile information and email address.</p>
                </div>
                <div class="card-body px-4 py-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Update Password</h6>
                    <p class="text-sm text-secondary mb-0">Ensure your account is using a long, random password to stay secure.</p>
                </div>
                <div class="card-body px-4 py-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Delete Account</h6>
                    <p class="text-sm text-secondary mb-0">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                </div>
                <div class="card-body px-4 py-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
