@extends('layouts.app')

@section('title', 'Advanced Theme Customization')
@section('page_name', 'Advanced Theme Customization')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card card-body mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Custom Color Palette</h5>
                    <a href="{{ route('clinic.profile') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
                
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                    <span class="alert-text">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <span class="alert-text">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                <div class="alert bg-gradient-info text-white border-0">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-crown fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1"><strong>Business Plan Feature</strong></h6>
                            <p class="mb-0">Customize your clinic's theme colors. Changes will be applied across your entire clinic interface.</p>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('themes.customize.update') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card p-3 mb-3">
                                <h6>Base Theme</h6>
                                <p class="text-sm text-muted">Choose a base theme to start with. Your custom colors will be applied on top of this.</p>
                                
                                <div class="d-flex gap-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="base_theme" id="base-light" value="default" {{ $baseTheme === 'default' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="base-light">
                                            Light
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="base_theme" id="base-dark" value="dark" {{ $baseTheme === 'dark' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="base-dark">
                                            Dark
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Custom Theme
                                </button>
                                <a href="{{ route('themes.customize.reset') }}" class="btn btn-outline-danger ms-2" onclick="return confirm('Are you sure you want to reset your custom theme?')">
                                    <i class="fas fa-undo me-1"></i> Reset to Default
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card p-3">
                                <h6>Preview</h6>
                                <div class="preview-container p-3 rounded" id="theme-preview" style="background-color: {{ $currentColors['background'] }};">
                                    <div class="preview-card mb-2 p-2 rounded" style="background-color: {{ $currentColors['card'] }}; color: {{ $currentColors['text'] }};">
                                        <h6 style="color: {{ $currentColors['text'] }};">Card Title</h6>
                                        <p class="text-sm" style="color: {{ $currentColors['textSecondary'] }};">This is how your content will look with the selected colors.</p>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm" style="background-color: {{ $currentColors['primary'] }}; color: #ffffff;">Primary</button>
                                            <button class="btn btn-sm" style="background-color: {{ $currentColors['secondary'] }}; color: #ffffff;">Secondary</button>
                                            <button class="btn btn-sm" style="background-color: {{ $currentColors['success'] }}; color: #ffffff;">Success</button>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm" style="background-color: {{ $currentColors['info'] }}; color: #ffffff;">Info</button>
                                        <button class="btn btn-sm" style="background-color: {{ $currentColors['warning'] }}; color: #ffffff;">Warning</button>
                                        <button class="btn btn-sm" style="background-color: {{ $currentColors['danger'] }}; color: #ffffff;">Danger</button>
                                    </div>
                                    <div class="d-flex mt-2">
                                        <div class="avatar avatar-sm me-2 rounded-circle d-flex justify-content-center align-items-center" style="background-color: {{ $currentColors['primary'] }}; width: 32px; height: 32px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div class="avatar avatar-sm me-2 rounded-circle d-flex justify-content-center align-items-center" style="background-color: {{ $currentColors['warning'] }}; width: 32px; height: 32px;">
                                            <i class="fas fa-cat text-white"></i>
                                        </div>
                                        <div class="avatar avatar-sm me-2 rounded-circle d-flex justify-content-center align-items-center" style="background-color: {{ $currentColors['success'] }}; width: 32px; height: 32px;">
                                            <i class="fas fa-dove text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Primary Colors -->
                        <div class="col-md-4 mb-3">
                            <label for="primary" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['primary'] }};"></span>
                                Primary
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="primary" name="primary" value="{{ $currentColors['primary'] }}" 
                                   data-preview-target="primary" data-bs-toggle="tooltip" title="Primary color">
                            @error('primary')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="secondary" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['secondary'] }};"></span>
                                Secondary
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="secondary" name="secondary" value="{{ $currentColors['secondary'] }}" 
                                   data-preview-target="secondary" data-bs-toggle="tooltip" title="Secondary color">
                            @error('secondary')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- State Colors -->
                        <div class="col-md-4 mb-3">
                            <label for="success" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['success'] }};"></span>
                                Success
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="success" name="success" value="{{ $currentColors['success'] }}" 
                                   data-preview-target="success" data-bs-toggle="tooltip" title="Success color">
                            @error('success')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="info" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['info'] }};"></span>
                                Info
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="info" name="info" value="{{ $currentColors['info'] }}" 
                                   data-preview-target="info" data-bs-toggle="tooltip" title="Info color">
                            @error('info')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="warning" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['warning'] }};"></span>
                                Warning
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="warning" name="warning" value="{{ $currentColors['warning'] }}" 
                                   data-preview-target="warning" data-bs-toggle="tooltip" title="Warning color">
                            @error('warning')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="danger" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['danger'] }};"></span>
                                Danger
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="danger" name="danger" value="{{ $currentColors['danger'] }}" 
                                   data-preview-target="danger" data-bs-toggle="tooltip" title="Danger color">
                            @error('danger')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Background Colors -->
                        <div class="col-md-4 mb-3">
                            <label for="background" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['background'] }}; border: 1px solid #dee2e6;"></span>
                                Background
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="background" name="background" value="{{ $currentColors['background'] }}" 
                                   data-preview-target="background" data-bs-toggle="tooltip" title="Background color">
                            @error('background')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="card" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['card'] }}; border: 1px solid #dee2e6;"></span>
                                Card
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="card" name="card" value="{{ $currentColors['card'] }}" 
                                   data-preview-target="card" data-bs-toggle="tooltip" title="Card color">
                            @error('card')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Text Colors -->
                        <div class="col-md-4 mb-3">
                            <label for="text" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['text'] }};"></span>
                                Text
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="text" name="text" value="{{ $currentColors['text'] }}" 
                                   data-preview-target="text" data-bs-toggle="tooltip" title="Text color">
                            @error('text')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="textSecondary" class="form-label d-flex align-items-center">
                                <span class="d-inline-block me-2 rounded" style="width: 20px; height: 20px; background-color: {{ $currentColors['textSecondary'] }};"></span>
                                Text Secondary
                            </label>
                            <input type="color" class="form-control form-control-color w-100" id="textSecondary" name="textSecondary" value="{{ $currentColors['textSecondary'] }}" 
                                   data-preview-target="textSecondary" data-bs-toggle="tooltip" title="Secondary text color">
                            @error('textSecondary')
                                <div class="text-danger text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Live preview of color changes
        const colorInputs = document.querySelectorAll('input[type="color"]');
        const preview = document.getElementById('theme-preview');
        const previewCard = preview.querySelector('.preview-card');
        const avatars = preview.querySelectorAll('.avatar');
        
        colorInputs.forEach(input => {
            input.addEventListener('input', function() {
                updatePreview(this.id, this.value);
            });
        });
        
        function updatePreview(colorId, value) {
            switch(colorId) {
                case 'background':
                    preview.style.backgroundColor = value;
                    break;
                case 'card':
                    previewCard.style.backgroundColor = value;
                    break;
                case 'text':
                    previewCard.style.color = value;
                    previewCard.querySelector('h6').style.color = value;
                    break;
                case 'textSecondary':
                    previewCard.querySelector('.text-sm').style.color = value;
                    break;
                case 'primary':
                    preview.querySelectorAll('.btn')[0].style.backgroundColor = value;
                    if (avatars.length > 0) avatars[0].style.backgroundColor = value;
                    break;
                case 'secondary':
                    preview.querySelectorAll('.btn')[1].style.backgroundColor = value;
                    break;
                case 'success':
                    preview.querySelectorAll('.btn')[2].style.backgroundColor = value;
                    if (avatars.length > 2) avatars[2].style.backgroundColor = value;
                    break;
                case 'info':
                    preview.querySelectorAll('.btn')[3].style.backgroundColor = value;
                    break;
                case 'warning':
                    preview.querySelectorAll('.btn')[4].style.backgroundColor = value;
                    if (avatars.length > 1) avatars[1].style.backgroundColor = value;
                    break;
                case 'danger':
                    preview.querySelectorAll('.btn')[5].style.backgroundColor = value;
                    break;
            }
        }
        
        // Update preview when base theme changes
        const baseThemeRadios = document.querySelectorAll('input[name="base_theme"]');
        baseThemeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    // You could make an AJAX call here to get the base theme colors
                    // For simplicity, we'll just change a few key colors based on light/dark
                    if (this.value === 'dark') {
                        if (!document.getElementById('background').dataset.userChanged) {
                            document.getElementById('background').value = '#1e1e2d';
                            updatePreview('background', '#1e1e2d');
                        }
                        if (!document.getElementById('card').dataset.userChanged) {
                            document.getElementById('card').value = '#2a2a3c';
                            updatePreview('card', '#2a2a3c');
                        }
                        if (!document.getElementById('text').dataset.userChanged) {
                            document.getElementById('text').value = '#e6e6e6';
                            updatePreview('text', '#e6e6e6');
                        }
                        if (!document.getElementById('textSecondary').dataset.userChanged) {
                            document.getElementById('textSecondary').value = '#b5b5c3';
                            updatePreview('textSecondary', '#b5b5c3');
                        }
                    } else {
                        if (!document.getElementById('background').dataset.userChanged) {
                            document.getElementById('background').value = '#f8f9fe';
                            updatePreview('background', '#f8f9fe');
                        }
                        if (!document.getElementById('card').dataset.userChanged) {
                            document.getElementById('card').value = '#ffffff';
                            updatePreview('card', '#ffffff');
                        }
                        if (!document.getElementById('text').dataset.userChanged) {
                            document.getElementById('text').value = '#344767';
                            updatePreview('text', '#344767');
                        }
                        if (!document.getElementById('textSecondary').dataset.userChanged) {
                            document.getElementById('textSecondary').value = '#67748e';
                            updatePreview('textSecondary', '#67748e');
                        }
                    }
                }
            });
        });
        
        // Track user changes to prevent auto-updates
        colorInputs.forEach(input => {
            input.addEventListener('change', function() {
                this.dataset.userChanged = 'true';
            });
        });
    });
</script>
@endpush 