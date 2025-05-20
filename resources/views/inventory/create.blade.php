@extends('layouts.app')

@section('title', 'Add New Inventory Item')
@section('page_name', 'Add New Inventory Item')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Add New Inventory Item</h6>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                </a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Error!</strong> Please check the form for errors.
                        <ul class="mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Name -->
                            <div class="form-group mb-3">
                                <label for="name" class="form-control-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                            </div>
                            
                            <!-- SKU -->
                            <div class="form-group mb-3">
                                <label for="sku" class="form-control-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="form-control" required>
                                <small class="form-text text-muted">Unique identifier for this item</small>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group mb-3">
                                <label for="category" class="form-control-label">Category</label>
                                <input type="text" name="category" id="category" list="category-list" value="{{ old('category') }}" class="form-control">
                                <datalist id="category-list">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}">
                                    @endforeach
                                </datalist>
                            </div>
                            
                            <!-- Active Status -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Quantity -->
                            <div class="form-group mb-3">
                                <label for="stock_quantity" class="form-control-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="form-control" required>
                            </div>
                            
                            <!-- Price -->
                            <div class="form-group mb-3">
                                <label for="selling_price" class="form-control-label">Price</label>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', 0) }}" min="0" step="0.01" class="form-control">
                            </div>
                            
                            <!-- Reorder Level -->
                            <div class="form-group mb-3">
                                <label for="reorder_level" class="form-control-label">Reorder Level</label>
                                <input type="number" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', 5) }}" min="0" class="form-control">
                                <small class="form-text text-muted">Alert will show when quantity falls below this level</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Optional Description -->
                    <div class="form-group mb-3">
                        <label for="description" class="form-control-label">Description (Optional)</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-sm btn-primary">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 