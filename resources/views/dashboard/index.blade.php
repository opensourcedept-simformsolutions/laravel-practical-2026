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
                <x-dashboard-card title="Total Societies" value="12"
                    icon="bi-buildings" bg="bg-primary-subtle"/>

                <x-dashboard-card title="Total Users" value="1524"
                    icon="bi-people-fill" bg="bg-success-subtle"/>

                <x-dashboard-card title="Total Residents" value="1245"
                    icon="bi-people" bg="bg-info-subtle"/>

                <x-dashboard-card title="Total Gatekeepers" value="38"
                    icon="bi-shield-check" bg="bg-warning-subtle"/>

                <x-dashboard-card title="Visitors Today" value="143"
                    icon="bi-person-check" bg="bg-primary-subtle"/>

                <x-dashboard-card title="Active Visitors" value="31"
                    icon="bi-door-open" bg="bg-success-subtle"/>

                <x-dashboard-card title="Open Complaints" value="17"
                    icon="bi-exclamation-circle" bg="bg-danger-subtle"/>

                <x-dashboard-card title="Pending Deliveries" value="26"
                    icon="bi-truck" bg="bg-secondary-subtle"/>
            </div>
        @else
            {{-- ADMIN DASHBOARD --}}
            @can('is-admin')
                <div class="row g-4">
                    <x-dashboard-card title="Total Residents" value="245"
                        icon="bi-people" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Total Flats" value="180"
                        icon="bi-house-door" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Visitors Today" value="18"
                        icon="bi-person-check" bg="bg-info-subtle"/>

                    <x-dashboard-card title="Visitors Inside Now" value="7"
                        icon="bi-door-open" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Deliveries Today" value="12"
                        icon="bi-box-seam" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" value="5"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="Open Complaints" value="9"
                        icon="bi-exclamation-circle" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Resolved Complaints" value="42"
                        icon="bi-check-circle" bg="bg-success-subtle"/>
                </div>

            @endcan

            {{-- RESIDENT DASHBOARD --}}
            @can('is-resident')
                <div class="row g-4">

                    <x-dashboard-card title="My Visitor Passes" value="24"
                        icon="bi-qr-code" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Visitors Expected Today" value="3"
                        icon="bi-calendar-check" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Active Visitors" value="1"
                        icon="bi-person-badge" bg="bg-info-subtle"/>

                    <x-dashboard-card title="My Deliveries" value="16"
                        icon="bi-box-seam" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" value="2"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="My Complaints" value="6"
                        icon="bi-chat-left-text" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Pending Complaints" value="2"
                        icon="bi-exclamation-triangle" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Resolved Complaints" value="4"
                        icon="bi-check-circle" bg="bg-success-subtle"/>

                </div>
            @endcan

            {{-- GATEKEEPER DASHBOARD --}}
            @can('is-gatekeeper')
                <div class="row g-4">

                    <x-dashboard-card title="Visitors Expected Today" value="14"
                        icon="bi-calendar-check" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Entries Today" value="10"
                        icon="bi-box-arrow-in-right" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Exits Today" value="8"
                        icon="bi-box-arrow-right" bg="bg-info-subtle"/>

                    <x-dashboard-card title="Visitors Inside Now" value="6"
                        icon="bi-door-open" bg="bg-warning-subtle"/>

                    <x-dashboard-card title="Pending Deliveries" value="9"
                        icon="bi-truck" bg="bg-danger-subtle"/>

                    <x-dashboard-card title="Deliveries Received" value="12"
                        icon="bi-box-seam" bg="bg-primary-subtle"/>

                    <x-dashboard-card title="Passes Verified" value="18"
                        icon="bi-patch-check" bg="bg-success-subtle"/>

                    <x-dashboard-card title="Rejected Entries" value="3"
                        icon="bi-x-circle" bg="bg-danger-subtle"/>

                </div>
            @endcan
        @endif
    </div>

@endsection
