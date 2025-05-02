@props(['count', 'limit', 'type'])

@php
$isUnlimited = $limit === 'Unlimited' || $limit === -1;
$hasReachedLimit = !$isUnlimited && $count >= $limit;
$isNearLimit = !$isUnlimited && $count >= ($limit * 0.8);
$badgeClass = $hasReachedLimit ? 'bg-danger' : ($isNearLimit ? 'bg-warning' : 'bg-success');
$iconClass = match($type) {
    'staff' => 'fas fa-users',
    'pets' => 'fas fa-paw',
    'clients' => 'fas fa-user-friends',
    'appointments' => 'fas fa-calendar-check',
    'inventory' => 'fas fa-boxes',
    default => 'fas fa-chart-bar'
};
@endphp

<div class="d-flex align-items-center">
    @if($isUnlimited)
        <span class="badge bg-success py-1 px-2">
            <i class="fas fa-infinity me-1"></i> Unlimited {{ $type }}
        </span>
    @else
        <div class="d-flex align-items-center">
            <span class="badge {{ $badgeClass }} py-1 px-2">
                <i class="{{ $iconClass }} me-1"></i> {{ $count }}/{{ $limit }} {{ $type }}
            </span>
            @if($hasReachedLimit)
                <a href="{{ route('subscription.index') }}" class="ms-2 text-xs text-primary">Upgrade plan</a>
            @endif
        </div>
    @endif
</div> 