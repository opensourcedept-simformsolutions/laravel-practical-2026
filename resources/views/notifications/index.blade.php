@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<style>
    .notification-item {
        transition: all 0.2s ease-in-out;
        border-left: 0 !important;
        position: relative;
        z-index: 1;
    }
    .notification-item:hover {
        background-color: #fafbfc !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        z-index: 5;
    }
    .notification-item:focus-within {
        z-index: 10;
    }
    .notification-item.unread {
        background-color: #f5f9ff !important; /* Very soft premium blue tint */
    }
    .notification-item.unread:hover {
        background-color: #ebf3fe !important;
    }
    .nav-tabs-notifications {
        border-bottom: 1px solid #eef2f6;
    }
    .nav-tabs-notifications .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        padding: 1rem 1.25rem;
        transition: all 0.2s ease;
        position: relative;
    }
    .nav-tabs-notifications .nav-link:hover {
        color: #1e293b;
        background: transparent;
    }
    .nav-tabs-notifications .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: transparent;
    }
    .notification-dot {
        width: 8px;
        height: 8px;
        background-color: #2563eb;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    .badge-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Notifications</h2>
            <p class="text-secondary mb-0 small">View and manage your system alerts and real-time requests</p>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        {{-- Notification Tabs --}}
        <div class="bg-white px-3 pt-2 nav-tabs-notifications">
            <ul class="nav nav-tabs border-0" id="notificationTabs">
                <li class="nav-item">
                    <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'all']) }}">
                        <i class="bi bi-bell me-1"></i> All
                        <span class="badge bg-light text-dark border ms-1 rounded-pill px-2 py-1">{{ auth()->user()->notifications()->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">
                        <i class="bi bi-envelope-exclamation me-1"></i> Unread
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge bg-danger text-white ms-1 rounded-pill px-2 py-1 badge-pulse">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @else
                            <span class="badge bg-light text-dark border ms-1 rounded-pill px-2 py-1">0</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $filter === 'read' ? 'active' : '' }}" href="{{ route('notifications.index', ['filter' => 'read']) }}">
                        <i class="bi bi-envelope-paper me-1"></i> Read
                        <span class="badge bg-light text-dark border ms-1 rounded-pill px-2 py-1">{{ auth()->user()->notifications()->whereNotNull('read_at')->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0 bg-white" id="notificationsContainer">
            @include('notifications._list')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function rd() {
        if (typeof window.loadNotifications === 'function') {
            window.loadNotifications(window.location.href);
        } else {
            location.reload();
        }
    }

    $(document).ready(function () {
        // Tab click ajax handler
        $(document).on('click', '#notificationTabs .nav-link', function (e) {
            e.preventDefault();
            let link = $(this);
            let url = link.attr('href');

            $('#notificationTabs .nav-link').removeClass('active');
            link.addClass('active');

            window.loadNotifications(url);
        });

        // Pagination click ajax handler
        $(document).on('click', '#notificationsContainer .pagination a', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            window.loadNotifications(url);
        });

        // AJAX Loader
        window.loadNotifications = function(url) {
            $('#notificationsContainer').css('opacity', '0.5');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                cache: false,
                success: function (response) {
                    $('#notificationsContainer').html(response.html).css('opacity', '1');
                    window.updateBadgeCounters(response.unread_count, response.read_count, response.all_count);
                    history.pushState(null, '', url);
                },
                error: function () {
                    $('#notificationsContainer').css('opacity', '1');
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to load notifications.'
                    });
                }
            });
        };

        // Helper: Update Badge Counters
        window.updateBadgeCounters = function(unread, read, all) {
            // All Tab
            let allTab = $('#notificationTabs .nav-link').eq(0);
            allTab.find('.badge').text(all);

            // Unread Tab
            let unreadTab = $('#notificationTabs .nav-link').eq(1);
            if (unread > 0) {
                unreadTab.find('.badge').replaceWith(`<span class="badge bg-danger text-white ms-1 rounded-pill px-2 py-1 badge-pulse">${unread}</span>`);
            } else {
                unreadTab.find('.badge').replaceWith('<span class="badge bg-light text-dark border ms-1 rounded-pill px-2 py-1">0</span>');
            }

            // Read Tab
            let readTab = $('#notificationTabs .nav-link').eq(2);
            readTab.find('.badge').text(read);

            // Sidebar Notification Badge Update
            let sidebarBadge = $('.sidebar-unread-badge'); 
            if (sidebarBadge.length > 0) {
                if (unread > 0) {
                    sidebarBadge.text(unread);
                } else {
                    sidebarBadge.remove();
                }
            } else if (unread > 0) {
                let sidebarLink = $('.sidebar-menu a[href*="/notifications"]');
                if (sidebarLink.length > 0) {
                    sidebarLink.find('.sidebar-link-label').append(`<span class="badge bg-danger rounded-pill badge-pulse ms-1 sidebar-unread-badge">${unread}</span>`);
                }
            }
        };

        // Mark as Read handler
        $(document).on('click', '.mark-read-btn', function () {
            let btn = $(this);
            let id = btn.data('id');
            let item = $(`#notification-${id}`);
            let currentFilter = new URLSearchParams(window.location.search).get('filter') || 'all';

            $.ajax({
                url: `/notifications/${id}/read`,
                type: 'POST',
                data: {
                    _method: 'PATCH'
                },
                success: function (response) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    // If we are in "unread" tab filter mode, slide the item away
                    if (currentFilter === 'unread') {
                        item.slideUp(300, function() {
                            $(this).remove();
                            // Load notifications again to fetch next page data if any, or trigger empty state
                            loadNotifications(window.location.href);
                        });
                    } else {
                        // Just toggle unread styling
                        item.removeClass('unread');
                        item.find('h6').removeClass('fw-bold text-dark').addClass('fw-normal text-secondary');
                        item.find('p').removeClass('text-dark fw-medium').addClass('text-muted');
                        item.find('.notification-dot').fadeOut(300);
                        btn.fadeOut(300, function() {
                            $(this).remove();
                        });

                        // Fetch updated counts
                        $.ajax({
                            url: window.location.href,
                            type: 'GET',
                            dataType: 'json',
                            cache: false,
                            success: function (res) {
                                updateBadgeCounters(res.unread_count, res.read_count, res.all_count);
                            }
                        });
                    }
                },
                error: function (xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Failed to mark notification as read.'
                    });
                }
            });
        });

        // Delete Notification handler
        $(document).on('click', '.delete-notification-btn', function () {
            let btn = $(this);
            let id = btn.data('id');
            let item = $(`#notification-${id}`);

            Swal.fire({
                title: 'Delete Notification?',
                text: "This notification will be permanently removed from your feed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/notifications/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        success: function (response) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });

                            item.slideUp(300, function() {
                                $(this).remove();
                                loadNotifications(window.location.href);
                            });
                        },
                        error: function (xhr) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Failed to delete notification.'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
