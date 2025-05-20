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
                    <form method="post" action="{{ route('tenant.profile.update') }}" class="mt-3">
                        @csrf
                        @method('patch')

                        <div class="form-group mb-3">
                            <label for="name" class="form-control-label">Name</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-control-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Save</button>

                            @if (session('status') === 'profile-updated')
                                <p class="text-sm text-success">Saved.</p>
                            @endif
                        </div>
                    </form>
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
                    <form method="post" action="{{ route('tenant.profile.password.update') }}" class="mt-3">
                        @csrf
                        @method('put')

                        <div class="form-group mb-3">
                            <label for="update_password_current_password" class="form-control-label">Current Password</label>
                            <div class="input-group">
                                <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                                <span class="input-group-text">
                                    <button type="button" onclick="togglePasswordVisibility('update_password_current_password')" class="btn btn-link p-0 m-0 text-dark" tabindex="-1">
                                        <i id="update_password_current_password-toggle-icon" class="fas fa-eye"></i>
                                    </button>
                                </span>
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="update_password_password" class="form-control-label">New Password</label>
                            <div class="input-group">
                                <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                                <span class="input-group-text">
                                    <button type="button" onclick="togglePasswordVisibility('update_password_password')" class="btn btn-link p-0 m-0 text-dark" tabindex="-1">
                                        <i id="update_password_password-toggle-icon" class="fas fa-eye"></i>
                                    </button>
                                </span>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="update_password_password_confirmation" class="form-control-label">Confirm Password</label>
                            <div class="input-group">
                                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                                <span class="input-group-text">
                                    <button type="button" onclick="togglePasswordVisibility('update_password_password_confirmation')" class="btn btn-link p-0 m-0 text-dark" tabindex="-1">
                                        <i id="update_password_password_confirmation-toggle-icon" class="fas fa-eye"></i>
                                    </button>
                                </span>
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Save</button>

                            @if (session('status') === 'password-updated')
                                <p class="text-sm text-success">Saved.</p>
                            @endif
                        </div>
                    </form>

                    <script>
                        function togglePasswordVisibility(inputId) {
                            const passwordInput = document.getElementById(inputId);
                            const icon = document.getElementById(inputId + '-toggle-icon');
                            
                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                passwordInput.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 