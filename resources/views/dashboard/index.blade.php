@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="container-fluid">

        <div class="mb-4">
            <h2 class="fw-bold">Dashboard</h2>
            <p class="text-muted mb-0">
                Welcome back, {{ auth()->user()->name }}
            </p>
        </div>

        @if (auth()->user()->isSuperAdmin())
            <div class="row g-4">
                <x-dashboard-card title="Total Societies" :value="$stats['total_societies']"
                    icon="bi-buildings" bg="bg-primary-subtle"/>

                <x-dashboard-card title="Total Users" :value="$stats['total_users']"
                    icon="bi-people-fill" bg="bg-success-subtle"/>

                <x-dashboard-card title="Total Residents" :value="$stats['total_residents']"
                    icon="bi-people" bg="bg-info-subtle"/>

                <x-dashboard-card title="Total Gatekeepers" :value="$stats['total_gatekeepers']"
                    icon="bi-shield-check" bg="bg-warning-subtle"/>
                <x-dashboard-card title="Visitors Today" :value="$stats['visitors_today']"
                    icon="bi-person-check" bg="bg-primary-subtle"/>

                <x-dashboard-card title="Active Visitors" :value="$stats['active_visitors']"
                    icon="bi-door-open" bg="bg-success-subtle"/>

                <x-dashboard-card title="Open Complaints" :value="$stats['open_complaints']"
                    icon="bi-exclamation-circle" bg="bg-danger-subtle"/>

                <x-dashboard-card title="Pending Deliveries" :value="$stats['pending_deliveries']"
                    icon="bi-truck" bg="bg-secondary-subtle"/>
            </div>
        @else
            {{-- ADMIN DASHBOARD --}}
            @can('is-admin')
                <div class="row g-4">
                    <x-dashboard-card title="Total Residents" :value="$stats['residents']"
                        icon="bi-people" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Total Flats" :value="$stats['flats']"
                        icon="bi-house-door" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Visitors Today" :value="$stats['visitors_today']"
                        icon="bi-person-check" bg="bg-info-subtle"/>

                    <x-dashboard-card title="Visitors Inside Now" :value="$stats['visitors_inside']"
                        icon="bi-door-open" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Deliveries Today" :value="$stats['deliveries_today']"
                        icon="bi-box-seam" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" :value="$stats['pending_deliveries']"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="Open Complaints" :value="$stats['open_complaints']"
                        icon="bi-exclamation-circle" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Resolved Complaints" :value="$stats['resolved_complaints']"
                        icon="bi-check-circle" bg="bg-success-subtle"/>
                </div>

            @endcan

            {{-- RESIDENT DASHBOARD --}}
            @can('is-resident')
                <div class="row g-4">

                    <x-dashboard-card title="My Visitor Passes" :value="$stats['my_visitor_passes']"
                        icon="bi-qr-code" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Visitors Expected Today" :value="$stats['visitors_expected_today']"
                        icon="bi-calendar-check" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Active Visitors" :value="$stats['active_visitors']"
                        icon="bi-person-badge" bg="bg-info-subtle"/>

                    <x-dashboard-card title="My Deliveries" :value="$stats['my_deliveries']"
                        icon="bi-box-seam" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" :value="$stats['pending_deliveries']"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="My Complaints" :value="$stats['my_complaints']"
                        icon="bi-chat-left-text" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Pending Complaints" :value="$stats['pending_complaints']"
                        icon="bi-exclamation-triangle" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Resolved Complaints" :value="$stats['resolved_complaints']"
                        icon="bi-check-circle" bg="bg-success-subtle"/>

                </div>
            @endcan

            {{-- GATEKEEPER DASHBOARD --}}
            @can('is-gatekeeper')
                <div class="row g-4">

                    <x-dashboard-card title="Visitors Expected Today" :value="$stats['visitors_expected_today']"
                        icon="bi-calendar-check" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Entries Today" :value="$stats['entries_today']"
                        icon="bi-box-arrow-in-right" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Exits Today" :value="$stats['exits_today']"
                        icon="bi-box-arrow-right" bg="bg-info-subtle"/>

                    <x-dashboard-card title="Visitors Inside Now" :value="$stats['visitors_inside_now']"
                        icon="bi-door-open" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" :value="$stats['pending_deliveries']"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="Deliveries Received" :value="$stats['deliveries_received']"
                        icon="bi-box-seam" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Passes Verified" :value="$stats['passes_verified']"
                        icon="bi-patch-check" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Rejected Entries" :value="$stats['rejected_entries']"
                        icon="bi-x-circle" bg="bg-danger-subtle"/>

                </div>
            @endcan
        @endif
    </div>

@endsection
