@extends('layouts.app', ['isSidebar' => true])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Item Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Item
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Name</th>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $item->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $item->description ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Quantity</th>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($item->quantity > 0)
                                            <span class="badge badge-success">In Stock</span>
                                        @else
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $item->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-tools {
        float: right;
    }
    .table th {
        background-color: #f8f9fa;
    }
    .badge {
        font-size: 0.9rem;
        padding: 0.5em 1em;
    }
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@endpush 