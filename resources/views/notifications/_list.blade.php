@if($notifications->count() > 0)
    <div class="list-group list-group-flush" id="notificationsList">
        @foreach($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $data = $notification->data;
                $type = $notification->type;
                $visitorLog = ($type === 'App\Notifications\VisitorStatusNotification') 
                    ? ($visitorLogs[$data['visitor_log_id'] ?? null] ?? null) 
                    : null;
            @endphp
            <div class="list-group-item p-3 border-bottom notification-item {{ $isUnread ? 'unread' : '' }}" 
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
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 {{ $isUnread ? 'fw-bold text-dark' : 'fw-normal text-secondary' }} d-flex align-items-center gap-2">
                                @if($type === 'App\Notifications\VisitorStatusNotification')
                                    Visitor Entry Request: {{ ucwords(str_replace('_', ' ', $visitorLog ? $visitorLog->status : $data['status'])) }}
                                @else
                                    System Notification
                                @endif
                                @if($isUnread)
                                    <span class="notification-dot"></span>
                                @endif
                            </h6>
                            <small class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0 {{ $isUnread ? 'text-dark fw-medium' : 'text-muted' }} small">
                            @if($type === 'App\Notifications\VisitorStatusNotification')
                                @php
                                    $statusVal = $visitorLog ? $visitorLog->status : $data['status'];
                                    $approver = $visitorLog && $visitorLog->approver ? $visitorLog->approver->name : ($data['approver_name'] ?? 'a resident');
                                @endphp
                                @if($statusVal === 'pending_approval')
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> is awaiting entry approval for flat <strong>{{ $data['flat_name'] }}</strong>.
                                @elseif($statusVal === 'approved')
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> has been <strong>approved</strong> by <strong>{{ $approver }}</strong> for flat <strong>{{ $data['flat_name'] }}</strong>.
                                @elseif($statusVal === 'rejected')
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> has been <strong>rejected</strong> by <strong>{{ $approver }}</strong> for flat <strong>{{ $data['flat_name'] }}</strong>.
                                @elseif($statusVal === 'entered')
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> has <strong>entered</strong> the society (approved by <strong>{{ $approver }}</strong>) for flat <strong>{{ $data['flat_name'] }}</strong>.
                                @elseif($statusVal === 'exited')
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> has <strong>exited</strong> the society for flat <strong>{{ $data['flat_name'] }}</strong>.
                                @else
                                    Visitor <strong>{{ $data['visitor_name'] }}</strong> status is <strong>{{ str_replace('_', ' ', $statusVal) }}</strong> for flat <strong>{{ $data['flat_name'] }}</strong>.
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
                            <button class="btn btn-success btn-sm btn-action text-white rounded-pill px-3"
                                    data-url="{{ route('passes.approve', $visitorLog->id) }}"
                                    data-method="PATCH"
                                    data-title="Approve Visitor?"
                                    data-text="Confirm you want to allow this visitor entry."
                                    data-confirm="Approve"
                                    data-success="Visitor approved successfully"
                                    data-color="#198754"
                                    title="Approve">
                                <i class="bi bi-check-lg me-1"></i> Approve
                            </button>
                            <button class="btn btn-danger btn-sm btn-action text-white rounded-pill px-3"
                                    data-url="{{ route('passes.reject', $visitorLog->id) }}"
                                    data-method="PATCH"
                                    data-title="Reject Visitor?"
                                    data-text="Confirm you want to deny this visitor entry."
                                    data-confirm="Reject"
                                    data-success="Visitor rejected successfully"
                                    data-color="#dc3545"
                                    title="Reject">
                                <i class="bi bi-x-lg me-1"></i> Reject
                            </button>
                        </div>
                    @endif
                    
                    <div class="col-auto action-read-container d-flex align-items-center gap-2">
                        @if($isUnread)
                            <button class="btn btn-sm btn-outline-success mark-read-btn border-0 rounded-circle" data-id="{{ $notification->id }}" title="Mark as Read" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="bi bi-check-lg fs-5"></i>
                            </button>
                        @endif
                        <button class="btn btn-sm btn-outline-danger delete-notification-btn border-0 rounded-circle" data-id="{{ $notification->id }}" title="Delete Notification" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-trash-fill fs-5"></i>
                        </button>
                    </div>
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
            @if($filter === 'unread')
                <i class="bi bi-envelope-open fs-1"></i>
            @elseif($filter === 'read')
                <i class="bi bi-envelope-slash fs-1"></i>
            @else
                <i class="bi bi-bell-slash fs-1"></i>
            @endif
        </div>
        <h5 class="fw-bold text-secondary">
            @if($filter === 'unread')
                All caught up!
            @elseif($filter === 'read')
                No read notifications
            @else
                No notifications yet
            @endif
        </h5>
        <p class="text-muted small mb-0">
            @if($filter === 'unread')
                You have no unread notifications right now.
            @elseif($filter === 'read')
                Notifications you read will appear here.
            @else
                You will receive system alerts and updates here.
            @endif
        </p>
    </div>
@endif
