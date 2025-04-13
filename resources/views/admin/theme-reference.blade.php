@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Theme Variables Reference</h6>
                    <p class="text-sm">This page displays all theme variables from ThemeServiceProvider</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="px-4 py-3">
                        <h4>Theme Colors</h4>
                        <div class="row">
                            @foreach($theme['colors'] as $colorName => $colorValue)
                            <div class="col-md-2 mb-3">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div style="height: 60px; width: 100%; background-color: {{ $colorValue }}; border-radius: 5px;"></div>
                                        <p class="text-xs font-weight-bold mt-2 mb-0">{{ $colorName }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $colorValue }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <h4 class="mt-4">Theme Gradients</h4>
                        <div class="row">
                            @foreach($theme['gradients'] as $gradientName => $gradientValue)
                            <div class="col-md-2 mb-3">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div style="height: 60px; width: 100%; background: {{ $gradientValue }}; border-radius: 5px;"></div>
                                        <p class="text-xs font-weight-bold mt-2 mb-0">{{ $gradientName }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ Str::limit($gradientValue, 20) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <h4 class="mt-4">Admin Menu Items</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Route</th>
                                        <th>Icon</th>
                                        <th>Color</th>
                                        <th>URL Matches</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adminMenu as $menuItem)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-shape icon-xs shadow border-radius-sm bg-{{ $menuItem['color'] }} me-2 d-flex align-items-center justify-content-center">
                                                    <i class="{{ $menuItem['icon'] }} text-white"></i>
                                                </div>
                                                {{ $menuItem['name'] }}
                                            </div>
                                        </td>
                                        <td><code>{{ $menuItem['route'] }}</code></td>
                                        <td><i class="{{ $menuItem['icon'] }}"></i> <code>{{ $menuItem['icon'] }}</code></td>
                                        <td><span class="badge bg-{{ $menuItem['color'] }}">{{ $menuItem['color'] }}</span></td>
                                        <td>
                                            @foreach($menuItem['matches'] as $match)
                                            <code class="d-block mb-1">{{ $match }}</code>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h4 class="mt-4">Other Theme Variables</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Variable</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Theme Name</td>
                                        <td>{{ $theme['name'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>Version</td>
                                        <td>{{ $theme['version'] ?? 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td>{{ $theme['description'] ?? 'Not set' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 