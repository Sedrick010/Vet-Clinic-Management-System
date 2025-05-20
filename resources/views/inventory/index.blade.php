@extends('layouts.app')

@section('title', 'Inventory Management')
@section('page_name', 'Inventory Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6>Inventory Management</h6>
                    <!-- Subscription Limit Indicator -->
                    @if(isset($inventoryLimit) && isset($inventoryCount))
                        <x-subscription-limit-indicator 
                            :count="$inventoryCount" 
                            :limit="$inventoryLimit" 
                            type="inventory" 
                        />
                    @endif
                </div>
                <div>
                    <a href="{{ route('inventory.pdf') }}" class="btn btn-sm btn-info me-2">
                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                    </a>
                    <a href="{{ route('inventory.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Item
                    </a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <!-- Filters and Search -->
                <div class="p-4">
                    <form action="{{ route('inventory.index') }}" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name, SKU, description, etc." 
                                class="form-control">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" {{ ($filters['category'] ?? '') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Inventory Table -->
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Name</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quantity</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($items->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        No inventory items found. <a href="{{ route('inventory.create') }}" class="text-primary">Add a new item</a>.
                                    </td>
                                </tr>
                            @else
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                @if($item->description)
                                                    <p class="text-xs text-secondary mb-0">{{ Str::limit($item->description, 50) }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $item->category ?? 'Uncategorized' }}</p>
                                        </td>
                                        <td>
                                            @if($item->isLowStock())
                                                <span class="text-xs font-weight-bold text-warning mb-0">
                                                    {{ $item->stock_quantity }}
                                                    <i class="fas fa-exclamation-triangle ms-1" title="Low stock"></i>
                                                </span>
                                            @elseif($item->isOutOfStock())
                                                <span class="text-xs font-weight-bold text-danger mb-0">
                                                    {{ $item->stock_quantity }}
                                                    <i class="fas fa-times-circle ms-1" title="Out of stock"></i>
                                                </span>
                                            @else
                                                <span class="text-xs font-weight-bold mb-0">{{ $item->stock_quantity }}</span>
                                            @endif
                                            <p class="text-xs text-secondary mb-0">Reorder at: {{ $item->reorder_level }}</p>
                                        </td>
                                        <td>
                                            @if($item->selling_price)
                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($item->selling_price, 2) }}</p>
                                            @else
                                                <p class="text-xs text-secondary mb-0">Not set</p>
                                            @endif
                                            @if($item->cost_price)
                                                <p class="text-xs text-secondary mb-0">Cost: {{ number_format($item->cost_price, 2) }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $item->sku }}</p>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-link text-info text-gradient px-2 mb-0" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-link text-dark px-2 mb-0" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger px-2 mb-0" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4 px-4">
                    {{ $items->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 