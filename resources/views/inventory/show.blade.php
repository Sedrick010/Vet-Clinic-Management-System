@extends('layouts.app')

@section('title', 'Inventory Item Details')
@section('page_name', 'Inventory Item Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Inventory Item Details</h6>
                <div>
                    <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Item
                    </a>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Item Header -->
                <div class="row mb-4 pb-3 border-bottom">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $item->name }}</h3>
                        @if($item->category)
                            <span class="badge bg-info text-white">{{ $item->category }}</span>
                        @endif
                        @if(isset($item->is_active) && !$item->is_active)
                            <span class="badge bg-danger ms-2">Inactive</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">SKU: {{ $item->sku }}</small><br>
                        <small class="text-muted">Last Updated: {{ $item->updated_at->format('M d, Y') }}</small>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="row">
                    <!-- Left Column - Basic Info -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                        
                        @if($item->description)
                            <div class="mb-4">
                                <h6 class="text-sm text-secondary">Description</h6>
                                <p>{{ $item->description }}</p>
                            </div>
                        @endif
                        
                        <div class="mb-4">
                            <h6 class="text-sm text-secondary">Status</h6>
                            <div>
                                @if(!isset($item->is_active) || $item->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Inventory Details -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Inventory Details</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-sm text-secondary">Quantity</h6>
                                <div class="d-flex align-items-center">
                                    <h3 class="{{ $item->isLowStock() ? 'text-warning' : ($item->isOutOfStock() ? 'text-danger' : '') }}">
                                        {{ $item->stock_quantity }}
                                    </h3>
                                    @if($item->isLowStock() && !$item->isOutOfStock())
                                        <i class="fas fa-exclamation-triangle text-warning ms-2" title="Low stock"></i>
                                    @elseif($item->isOutOfStock())
                                        <i class="fas fa-times-circle text-danger ms-2" title="Out of stock"></i>
                                    @endif
                                </div>
                                <small class="text-muted">Reorder level: {{ $item->reorder_level }}</small>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6 class="text-sm text-secondary">Pricing</h6>
                                <div>
                                    <h3>{{ isset($item->selling_price) ? '$'.number_format($item->selling_price, 2) : 'Not set' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <div>
                        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete()" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Delete Item
                            </button>
                        </form>
                    </div>
                    <div>
                        @if(isset($item->is_active))
                            <form action="{{ route('inventory.toggle-status', $item->id) }}" method="POST" class="d-inline me-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn {{ $item->is_active ? 'btn-warning' : 'btn-success' }}">
                                    <i class="fas {{ $item->is_active ? 'fa-ban me-2' : 'fa-check-circle me-2' }}"></i>
                                    {{ $item->is_active ? 'Deactivate Item' : 'Activate Item' }}
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Item
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endsection 