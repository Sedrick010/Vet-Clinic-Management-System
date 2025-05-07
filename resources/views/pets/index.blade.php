@extends('layouts.app')

@section('title', 'Pets')
@section('page_name', 'Pets')

@php
    $isSidebar = true;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>All Pets</h6>
                        <!-- Subscription Limit Indicator -->
                        @if(isset($petsLimit) && isset($petsCount))
                            <x-subscription-limit-indicator 
                                :count="$petsCount" 
                                :limit="$petsLimit" 
                                type="pets" 
                            />
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('pets.all-pdf') }}" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('pets.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Pet
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 mt-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 mt-3">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="table-responsive p-0">
                        @if($pets->count() > 0)
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pet</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Owner</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Species/Breed</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Gender</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Age</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pets as $pet)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <div class="avatar avatar-sm me-3 bg-{{ $pet->species == 'Dog' ? 'primary' : ($pet->species == 'Cat' ? 'warning' : ($pet->species == 'Bird' ? 'success' : ($pet->species == 'Reptile' ? 'info' : 'secondary'))) }} rounded-circle d-flex justify-content-center align-items-center" style="background-color: var(--{{ $pet->species == 'Dog' ? 'primary' : ($pet->species == 'Cat' ? 'warning' : ($pet->species == 'Bird' ? 'success' : ($pet->species == 'Reptile' ? 'info' : 'secondary'))) }}-color) !important;">
                                                            <i class="text-white fas 
                                                                @if($pet->species == 'Dog') fa-dog
                                                                @elseif($pet->species == 'Cat') fa-cat
                                                                @elseif($pet->species == 'Bird') fa-dove
                                                                @elseif($pet->species == 'Reptile') fa-dragon
                                                                @else fa-paw
                                                                @endif">
                                                            </i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center ms-3">
                                                        <h6 class="mb-0 text-sm">{{ $pet->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ optional($pet->owner)->name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $pet->species }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $pet->breed }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ ucfirst($pet->gender ?? 'Unknown') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    @if($pet->birthdate)
                                                        {{ $pet->birthdate->age }} years
                                                    @else
                                                        Unknown
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <a href="{{ route('pets.show', $pet->id) }}" class="btn btn-link text-secondary mb-0 me-2">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </a>
                                                    <a href="{{ route('pets.edit', $pet->id) }}" class="btn btn-link text-secondary mb-0 me-2">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('pets.destroy', $pet->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-secondary mb-0" 
                                                            onclick="return confirm('Are you sure you want to delete this pet?')">
                                                            <i class="fas fa-trash text-danger text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $pets->links() }}
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="icon mb-2">
                                    <i class="fas fa-paw fa-3x text-secondary"></i>
                                </div>
                                <h6 class="text-secondary">No pets found</h6>
                                <a href="{{ route('pets.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="fas fa-plus me-1"></i> Add First Pet
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 