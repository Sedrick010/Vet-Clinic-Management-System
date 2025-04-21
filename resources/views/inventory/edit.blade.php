@extends('layouts.app')

@section('title', 'Edit Inventory Item')
@section('page_name', 'Edit Inventory Item')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6>Edit Inventory Item</h6>
                <div>
                    <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye me-2"></i>View Item
                    </a>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                    </a>
                </div>
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

                <form action="{{ route('inventory.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Name -->
                            <div class="form-group mb-3">
                                <label for="name" class="form-control-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" class="form-control" required>
                            </div>
                            
                            <!-- SKU -->
                            <div class="form-group mb-3">
                                <label for="sku" class="form-control-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku', $item->sku) }}" class="form-control" required>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group mb-3">
                                <label for="category" class="form-control-label">Category</label>
                                <input type="text" name="category" id="category" list="category-list" value="{{ old('category', $item->category) }}" class="form-control">
                                <datalist id="category-list">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}">
                                    @endforeach
                                </datalist>
                            </div>
                            
                            <!-- Active Status -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Quantity -->
                            <div class="form-group mb-3">
                                <label for="stock_quantity" class="form-control-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', $item->stock_quantity) }}" min="0" class="form-control" required>
                            </div>
                            
                            <!-- Price -->
                            <div class="form-group mb-3">
                                <label for="selling_price" class="form-control-label">Price</label>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', $item->selling_price) }}" min="0" step="0.01" class="form-control">
                            </div>
                            
                            <!-- Reorder Level -->
                            <div class="form-group mb-3">
                                <label for="reorder_level" class="form-control-label">Reorder Level</label>
                                <input type="number" name="reorder_level" id="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}" min="0" class="form-control">
                                <small class="form-text text-muted">Alert will show when quantity falls below this level</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Optional Description -->
                    <div class="form-group mb-3">
                        <label for="description" class="form-control-label">Description (Optional)</label>
                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $item->description) }}</textarea>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <button type="button" onclick="confirmDelete()" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Delete Item
                            </button>
                        </div>
                        <div>
                            <a href="{{ route('inventory.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Item</button>
                        </div>
                    </div>
                </form>

                <!-- Delete form moved outside the main form -->
                <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-none" id="delete-form">
                    @csrf
                    @method('DELETE')
                </form>
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