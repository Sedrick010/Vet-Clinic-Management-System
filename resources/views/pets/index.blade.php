@extends('layouts.app')

@section('page_name', 'Pets')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">All Pets</h6>
                    <a href="{{ route('pets.create') }}" class="btn btn-sm bg-gradient-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add New Pet
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success mx-4 mt-3 text-white">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger mx-4 mt-3 text-white">
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
                                                        <div class="icon icon-shape icon-sm text-center border-radius-md text-white" style="background-color: 
                                                            @if($pet->species == 'Dog') #3498db
                                                            @elseif($pet->species == 'Cat') #f39c12
                                                            @elseif($pet->species == 'Bird') #2ecc71
                                                            @elseif($pet->species == 'Reptile') #27ae60
                                                            @else #95a5a6
                                                            @endif
                                                        ">
                                                            <i class="fas 
                                                                @if($pet->species == 'Dog') fa-dog
                                                                @elseif($pet->species == 'Cat') fa-cat
                                                                @elseif($pet->species == 'Bird') fa-dove
                                                                @elseif($pet->species == 'Reptile') fa-dragon
                                                                @else fa-paw
                                                                @endif
                                                                opacity-10">
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
                                                    <a href="{{ route('pets.show', $pet->id) }}" class="icon-link me-2" data-bs-toggle="tooltip" data-bs-title="View details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('pets.edit', $pet->id) }}" class="icon-link me-2" data-bs-toggle="tooltip" data-bs-title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pets.destroy', $pet->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link icon-link p-0 m-0" 
                                                            onclick="return confirm('Are you sure you want to delete this pet?')"
                                                            data-bs-toggle="tooltip" data-bs-title="Delete">
                                                            <i class="fas fa-trash text-danger"></i>
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
                                    <i class="fas fa-paw fa-3x" style="color: var(--bs-gray-400);"></i>
                                </div>
                                <h6 class="text-secondary">No pets found</h6>
                                <a href="{{ route('pets.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus-circle me-1"></i> Add First Pet
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