@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Theme Settings</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('tenant.theme.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_color">Primary Color</label>
                                    <input type="color" class="form-control" id="primary_color" name="primary_color" 
                                           value="{{ $theme->primary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secondary_color">Secondary Color</label>
                                    <input type="color" class="form-control" id="secondary_color" name="secondary_color" 
                                           value="{{ $theme->secondary_color }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="accent_color">Accent Color</label>
                                    <input type="color" class="form-control" id="accent_color" name="accent_color" 
                                           value="{{ $theme->accent_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="text_color">Text Color</label>
                                    <input type="color" class="form-control" id="text_color" name="text_color" 
                                           value="{{ $theme->text_color }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="background_color">Background Color</label>
                                    <input type="color" class="form-control" id="background_color" name="background_color" 
                                           value="{{ $theme->background_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_dark_mode" name="is_dark_mode" 
                                               {{ $theme->is_dark_mode ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_dark_mode">Dark Mode</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Save Theme Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 