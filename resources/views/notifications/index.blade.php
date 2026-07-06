@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Notifications</h2>
            <p class="text-muted mb-0">View and manage your system notifications</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush rounded-3">
                    @foreach($notifications as $notification)
                        @php
                            $isUnread = is_null($notification->read_at);
                            $data = $notification->data;
                            $type = $notification->type;
                            $visitorLog = ($type === 'App\Notifications\VisitorStatusNotification') 
                                ? ($visitorLogs[$data['visitor_log_id'] ?? null] ?? null) 
                                : null;
                        @endphp
                        <div class="list-group-item p-3 border-bottom notification-item {{ $isUnread ? 'bg-light border-start border-primary border-4' : '' }}" 
                             id="notification-{{ $notification->id }}">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                        @if($type === 'App\Notifications\VisitorStatusNotification')
                                            <i class="bi bi-person-check fs-4"></i>
                                        @else
                                            <i class="bi bi-bell fs-4"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col text-start">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1 fw-bold text-dark">
                                            @if($type === 'App\Notifications\VisitorStatusNotification')
                                                Visitor Entry Request: {{ ucfirst($visitorLog ? $visitorLog->status : $data['status']) }}
                                            @else
                                                System Notification
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-secondary small">
                                        @if($type === 'App\Notifications\VisitorStatusNotification')
                                            @if($visitorLog && $visitorLog->status === 'pending_approval')
                                                Visitor <strong>{{ $data['visitor_name'] }}</strong> is awaiting entry approval for flat <strong>{{ $data['flat_name'] }}</strong>.
                                            @else
                                                Visitor <strong>{{ $data['visitor_name'] }}</strong> has been <strong>{{ $visitorLog ? $visitorLog->status : $data['status'] }}</strong> by <strong>{{ $visitorLog && $visitorLog->approver ? $visitorLog->approver->name : ($data['approver_name'] ?? 'a resident') }}</strong> for flat <strong>{{ $data['flat_name'] }}</strong>.
                                            @endif
                                        @elseif($type === 'App\Notifications\ResidentWelcomeNotification')
                                            Welcome to SocietyMS! Your resident account has been set up successfully.
                                        @else
                                            {{ json_encode($data) }}
                                        @endif
                                    </p>
                                </div>
                                @if($visitorLog && $visitorLog->status === 'pending_approval' && auth()->user()->isResident())
                                    <div class="col-auto d-flex gap-2">
                                        <button class="btn btn-success btn-sm btn-action text-white"
                                                data-url="{{ route('passes.approve', $visitorLog->id) }}"
                                                data-method="PATCH"
                                                data-title="Approve Visitor?"
                                                data-text="Confirm you want to allow this visitor entry."
                                                data-confirm="Approve"
                                                data-success="Visitor approved successfully"
                                                data-color="#198754"
                                                title="Approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-action text-white"
                                                data-url="{{ route('passes.reject', $visitorLog->id) }}"
                                                data-method="PATCH"
                                                data-title="Reject Visitor?"
                                                data-text="Confirm you want to deny this visitor entry."
                                                data-confirm="Reject"
                                                data-success="Visitor rejected successfully"
                                                data-color="#dc3545"
                                                title="Reject">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </div>
                                @endif
                                @if($isUnread)
                                    <div class="col-auto">
                                        <button class="btn btn-outline-secondary btn-sm mark-read-btn" 
                                                data-id="{{ $notification->id }}" 
                                                title="Mark as Read">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="avatar rounded-circle bg-light text-muted p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="bi bi-bell-slash fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-secondary">No notifications yet</h5>
                    <p class="text-muted small">You will receive system alerts and updates here.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Override the global layout reload function to refresh the notifications page on action success
    function rd() {
        location.reload();
    }

    $(document).ready(function () {
        $('.mark-read-btn').on('click', function () {
            let btn = $(this);
            let id = btn.data('id');
            let item = $(`#notification-${id}`);

            $.ajax({
                url: `/notifications/${id}/read`,
                type: 'POST',
                data: {
                    _method: 'PATCH'
                },
                success: function (response) {
                    // Update styling
                    item.removeClass('bg-light border-start border-primary border-4');
                    btn.fadeOut();
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                },
                error: function (xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to mark notification as read.'
                    });
                }
            });
        });
    });
</script>
@endpush
