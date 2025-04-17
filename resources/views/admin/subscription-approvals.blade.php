@extends('layouts.app')

@section('title', 'Subscription Approvals')
@section('page_name', 'Subscription Approvals')

@section('content')
<div class="container-fluid py-4">
    <!-- Pending Subscriptions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="text-warning">Pending Subscriptions</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Requested By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $pendingFound = false; @endphp
                                @foreach($subscriptions as $subscription)
                                    @if($subscription->approval_status === 'pending')
                                        @php $pendingFound = true; @endphp
                                        <tr data-subscription-id="{{ $subscription->id }}">
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm clinic-name">{{ $subscription->clinic->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0 clinic-email">{{ $subscription->clinic->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 plan-name">{{ $subscription->plan_name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 amount">${{ number_format($subscription->amount, 2) }}/month</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 user-name">{{ $subscription->user->name }}</p>
                                                <p class="text-xs text-secondary mb-0 user-email">{{ $subscription->user->email }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 created-date">{{ $subscription->created_at->format('M d, Y') }}</p>
                                                <p class="text-xs text-secondary mb-0 created-time">{{ $subscription->created_at->format('h:i A') }}</p>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm" onclick="approveSubscription({{ $subscription->id }})">
                                                    Approve
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="rejectSubscription({{ $subscription->id }})">
                                                    Reject
                                                </button>
                                                <button type="button" class="btn btn-dark btn-sm" onclick="deleteSubscription({{ $subscription->id }})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(!$pendingFound)
                                    <tr data-empty="true">
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-sm mb-0">No pending subscriptions</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Subscriptions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="text-success">Approved Subscriptions</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Requested By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Approved At</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $approvedFound = false; @endphp
                                @foreach($subscriptions as $subscription)
                                    @if($subscription->approval_status === 'approved')
                                        @php $approvedFound = true; @endphp
                                        <tr data-subscription-id="{{ $subscription->id }}">
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm clinic-name">{{ $subscription->clinic->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0 clinic-email">{{ $subscription->clinic->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 plan-name">{{ $subscription->plan_name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 amount">${{ number_format($subscription->amount, 2) }}/month</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 user-name">{{ $subscription->user->name }}</p>
                                                <p class="text-xs text-secondary mb-0 user-email">{{ $subscription->user->email }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 created-date">{{ $subscription->created_at->format('M d, Y') }}</p>
                                                <p class="text-xs text-secondary mb-0 created-time">{{ $subscription->created_at->format('h:i A') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $subscription->approved_at->format('M d, Y') }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $subscription->approved_at->format('h:i A') }}</p>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-dark btn-sm" onclick="deleteSubscription({{ $subscription->id }})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(!$approvedFound)
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-sm mb-0">No approved subscriptions</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejected Subscriptions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="text-danger">Rejected Subscriptions</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Clinic</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Plan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Requested By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Rejected At</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rejectedFound = false; @endphp
                                @foreach($subscriptions as $subscription)
                                    @if($subscription->approval_status === 'rejected')
                                        @php $rejectedFound = true; @endphp
                                        <tr data-subscription-id="{{ $subscription->id }}">
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm clinic-name">{{ $subscription->clinic->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0 clinic-email">{{ $subscription->clinic->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 plan-name">{{ $subscription->plan_name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 amount">${{ number_format($subscription->amount, 2) }}/month</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 user-name">{{ $subscription->user->name }}</p>
                                                <p class="text-xs text-secondary mb-0 user-email">{{ $subscription->user->email }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 created-date">{{ $subscription->created_at->format('M d, Y') }}</p>
                                                <p class="text-xs text-secondary mb-0 created-time">{{ $subscription->created_at->format('h:i A') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $subscription->rejected_at->format('M d, Y') }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $subscription->rejected_at->format('h:i A') }}</p>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-dark btn-sm" onclick="deleteSubscription({{ $subscription->id }})">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if(!$rejectedFound)
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-sm mb-0">No rejected subscriptions</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function updateSubscriptionRow(subscription, action) {
    try {
        const row = document.querySelector(`tr[data-subscription-id="${subscription.id}"]`);
        if (!row) {
            console.error('Row not found for subscription:', subscription.id);
            return false;
        }

        // Create new row for approved/rejected table
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-subscription-id', subscription.id);
        
        const date = new Date();
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedTime = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });

        const clinicName = row.querySelector('.clinic-name')?.textContent;
        const clinicEmail = row.querySelector('.clinic-email')?.textContent;
        const planName = row.querySelector('.plan-name')?.textContent;
        const amount = row.querySelector('.amount')?.textContent;
        const userName = row.querySelector('.user-name')?.textContent;
        const userEmail = row.querySelector('.user-email')?.textContent;
        const createdDate = row.querySelector('.created-date')?.textContent;
        const createdTime = row.querySelector('.created-time')?.textContent;

        if (!clinicName || !clinicEmail || !planName || !amount || !userName || !userEmail || !createdDate || !createdTime) {
            console.error('Missing required data from row');
            return false;
        }

        newRow.innerHTML = `
            <td>
                <div class="d-flex px-3 py-1">
                    <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm clinic-name">${clinicName}</h6>
                        <p class="text-xs text-secondary mb-0 clinic-email">${clinicEmail}</p>
                    </div>
                </div>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 plan-name">${planName}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 amount">${amount}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 user-name">${userName}</p>
                <p class="text-xs text-secondary mb-0 user-email">${userEmail}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0 created-date">${createdDate}</p>
                <p class="text-xs text-secondary mb-0 created-time">${createdTime}</p>
            </td>
            <td>
                <p class="text-sm font-weight-bold mb-0">${formattedDate}</p>
                <p class="text-xs text-secondary mb-0">${formattedTime}</p>
            </td>
            <td>
                <button type="button" class="btn btn-dark btn-sm" onclick="deleteSubscription(${subscription.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;

        // Add to appropriate table
        const targetTable = action === 'approved' ? 
            document.querySelector('.text-success').closest('.card').querySelector('tbody') :
            document.querySelector('.text-danger').closest('.card').querySelector('tbody');

        if (!targetTable) {
            console.error('Target table not found');
            return false;
        }

        // Remove "No approved/rejected subscriptions" message if it exists
        const noDataRow = targetTable.querySelector('tr td[colspan="7"]');
        if (noDataRow) {
            noDataRow.closest('tr').remove();
        }

        targetTable.insertBefore(newRow, targetTable.firstChild);

        // Remove from pending table
        row.remove();

        // Check if pending table is empty
        const pendingTable = document.querySelector('.text-warning').closest('.card').querySelector('tbody');
        if (!pendingTable.querySelector('tr:not([data-empty="true"])')) {
            pendingTable.innerHTML = `
                <tr data-empty="true">
                    <td colspan="7" class="text-center py-4">
                        <p class="text-sm mb-0">No pending subscriptions</p>
                    </td>
                </tr>
            `;
        }

        return true;
    } catch (error) {
        console.error('Error updating subscription row:', error);
        return false;
    }
}

function approveSubscription(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to approve this subscription?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/subscription/approve/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const updated = updateSubscriptionRow({ id }, 'approved');
                    if (updated) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error(data.message || 'Failed to approve subscription');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred. The page will refresh to show the current status.',
                    icon: 'error'
                }).then(() => {
                    window.location.reload();
                });
            });
        }
    });
}

function rejectSubscription(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to reject this subscription?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reject it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/subscription/reject/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const updated = updateSubscriptionRow({ id }, 'rejected');
                    if (updated) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error(data.message || 'Failed to reject subscription');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred. The page will refresh to show the current status.',
                    icon: 'error'
                }).then(() => {
                    window.location.reload();
                });
            });
        }
    });
}

function deleteSubscription(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This subscription will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/subscription/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-subscription-id="${id}"]`);
                    if (row) {
                        const tbody = row.closest('tbody');
                        row.remove();

                        // Check if table is empty
                        if (!tbody.querySelector('tr:not([data-empty="true"])')) {
                            const tableType = tbody.closest('.card').querySelector('.card-header h6').classList.contains('text-warning') ? 'pending' :
                                            tbody.closest('.card').querySelector('.card-header h6').classList.contains('text-success') ? 'approved' : 'rejected';
                            
                            tbody.innerHTML = `
                                <tr data-empty="true">
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-sm mb-0">No ${tableType} subscriptions</p>
                                    </td>
                                </tr>
                            `;
                        }
                    }

                    Swal.fire(
                        'Deleted!',
                        'Subscription has been deleted.',
                        'success'
                    );
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'An error occurred while deleting the subscription.',
                    'error'
                );
            });
        }
    });
}
</script>
@endpush 