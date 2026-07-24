<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-white">
            <i class="bi bi-buildings-fill text-primary"></i>
            <span class="sidebar-brand-text">SocietyMS</span>
        </a>
        <button type="button" class="btn text-white p-0 d-lg-none" id="sidebarClose" aria-label="Close sidebar">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <div class="sidebar-menu">

        {{-- Section: Core --}}
        <div class="sidebar-section-header">Core</div>

        {{-- Dashboard --}}
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="bi bi-speedometer2"></i>
            <span class="sidebar-link-label">Dashboard</span>
        </x-sidebar-link>

        {{-- Notifications --}}
        <x-sidebar-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
            <i class="bi bi-bell-fill"></i>
            <span class="sidebar-link-label d-flex justify-content-between align-items-center w-100">
                <span>Notifications</span>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="badge bg-danger rounded-pill badge-pulse ms-1 sidebar-unread-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </span>
        </x-sidebar-link>

        {{-- Section: Society Management --}}
        @canany(['societies.view', 'wings.view', 'flats.view', 'residents.view', 'users.view'])
            <div class="sidebar-section-header">Society Admin</div>

            @can('societies.view')
                {{-- Society --}}
                <x-sidebar-link :href="route('societies.index')" :active="request()->routeIs('societies.index')">
                    <i class="bi bi-buildings-fill"></i>
                    <span class="sidebar-link-label">Society</span>
                </x-sidebar-link>
            @endcan

            @can('wings.view')
                {{-- Wings --}}
                <x-sidebar-link :href="route('wings.index')" :active="request()->routeIs('wings.*')">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span class="sidebar-link-label">Wings</span>
                </x-sidebar-link>
            @endcan

            @can('flats.view')
                {{-- Flats --}}
                <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.index')">
                    <i class="bi bi-building-fill"></i>
                    <span class="sidebar-link-label">Flats</span>
                </x-sidebar-link>
            @endcan

            @can('residents.view')
                {{-- Residents --}}
                <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.index')">
                    <i class="bi bi-people-fill"></i>
                    <span class="sidebar-link-label">Residents</span>
                </x-sidebar-link>
            @endcan

            @can('users.view')
                {{-- Users Management --}}
                <x-sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.index')">
                    <i class="bi bi-person-badge-fill"></i>
                    <span class="sidebar-link-label">Users Management</span>
                </x-sidebar-link>
            @endcan
        @endcanany

        {{-- Section: Permissions --}}
        @canany(['is-super-admin', 'permissions.manage'])
            <div class="sidebar-section-header">Permissions</div>

            @can('is-super-admin')
                {{-- System Permissions --}}
                <x-sidebar-link :href="route('admin.system-permissions.index')" :active="request()->routeIs('admin.system-permissions.*')">
                    <i class="bi bi-key-fill"></i>
                    <span class="sidebar-link-label">System Permissions</span>
                </x-sidebar-link>

                {{-- Role Defaults --}}
                <x-sidebar-link :href="route('admin.role-permissions.index')" :active="request()->routeIs('admin.role-permissions.*')">
                    <i class="bi bi-shield-check"></i>
                    <span class="sidebar-link-label">Role Defaults</span>
                </x-sidebar-link>

                {{-- Database Backups --}}
                <x-sidebar-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups.*')">
                    <i class="bi bi-database-fill-gear"></i>
                    <span class="sidebar-link-label">Database Backups</span>
                </x-sidebar-link>

                {{-- API Key Management --}}
                <x-sidebar-link :href="route('admin.api-keys.index')" :active="request()->routeIs('admin.api-keys.*')">
                    <i class="bi bi-cpu-fill"></i>
                    <span class="sidebar-link-label">API Key Management</span>
                </x-sidebar-link>
            @endcan

            @can('permissions.manage')
                {{-- User Permissions --}}
                <x-sidebar-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')">
                    <i class="bi bi-person-gear"></i>
                    <span class="sidebar-link-label">User Permissions</span>
                </x-sidebar-link>
            @endcan
        @endcanany

        {{-- Section: Visitor Control --}}
        @canany(['passes.view', 'visitor_logs.view'])
            <div class="sidebar-section-header">Visitor Control</div>

            @can('passes.view')
                {{-- Visitor Passes --}}
                <x-sidebar-link :href="route('passes.index')" :active="request()->routeIs('passes.*')">
                    <i class="bi bi-person-vcard-fill"></i>
                    <span class="sidebar-link-label">Visitor Passes</span>
                </x-sidebar-link>
            @endcan

            @can('visitor_logs.view')
                {{-- Pending Passes --}}
                <x-sidebar-link :href="route('gatekeeper.visitor-logs.pending')" :active="request()->routeIs('gatekeeper.visitor-logs.*')">
                    <i class="bi bi-person-check-fill"></i>
                    <span class="sidebar-link-label">Pending Passes</span>
                </x-sidebar-link>

                {{-- Scan Visitor Pass --}}
                <x-sidebar-link :href="route('gatekeeper.scan')" :active="request()->routeIs('gatekeeper.scan')">
                    <i class="bi bi-qr-code-scan"></i>
                    <span class="sidebar-link-label">Scan Visitor Pass</span>
                </x-sidebar-link>
            @endcan
        @endcanany

        {{-- Section: Operations --}}
        @canany(['deliveries.view', 'complaints.view', 'activity_logs.view', 'auth_audit.view'])
            <div class="sidebar-section-header">Operations</div>

            @can('deliveries.view')
                {{-- Delivery --}}
                <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
                    <i class="bi bi-box-seam-fill"></i>
                    <span class="sidebar-link-label">Delivery</span>
                </x-sidebar-link>
            @endcan

            @can('complaints.view')
                {{-- Complaints --}}
                <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <span class="sidebar-link-label">Complaints</span>
                </x-sidebar-link>
            @endcan

            {{-- Activity Logs --}}
            @can('activity_logs.view')
                <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')">
                    <i class="bi bi-clock-history"></i>
                    <span class="sidebar-link-label">Activity Logs</span>
                </x-sidebar-link>
            @endcan

            @can('auth_audit.view')
                <x-sidebar-link :href="route('admin.auth-audit.index')" :active="request()->routeIs('admin.auth-audit.*')">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span class="sidebar-link-label">Auth Audit Logs</span>
                </x-sidebar-link>
            @endcan
        @endcanany

        {{-- Section: Analytics --}}
        @can('reports.view')
            <div class="sidebar-section-header">Analytics</div>

            {{-- Reports --}}
            @php
                $reportsActive = request()->routeIs('reports.*');
            @endphp
            <div class="sidebar-dropdown">
                <button type="button" class="sidebar-dropdown-toggle {{ $reportsActive ? 'active' : '' }}" id="reportsToggle">
                    <span>
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span class="sidebar-link-label">Reports</span>
                    </span>

                    <i class="bi bi-chevron-down {{ $reportsActive ? 'rotate-180' : '' }}" id="reportsArrow"></i>
                </button>

                <div class="sidebar-dropdown-menu" style="{{ $reportsActive ? 'display: block;' : 'display: none;' }}" id="reportsMenu">

                    <x-sidebar-link :href="route('reports.deliveries.')" :active="request()->routeIs('reports.deliveries*')">
                        <i class="bi bi-box-seam-fill"></i>
                        <span class="sidebar-link-label">Delivery Report</span>
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('reports.complaints.')" :active="request()->routeIs('reports.complaints*')">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <span class="sidebar-link-label">Complaint Report</span>
                    </x-sidebar-link>

                    <x-sidebar-link :href="route('reports.passes.')" :active="request()->routeIs('reports.passes*')">
                        <i class="bi bi-person-vcard-fill"></i>
                        <span class="sidebar-link-label">Visitor Report</span>
                    </x-sidebar-link>

                </div>

            </div>
        @endcan

    </div>
</aside>
