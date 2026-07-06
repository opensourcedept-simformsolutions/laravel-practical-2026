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
        @canany(['is-super-admin', 'is-admin'])
            <div class="sidebar-section-header">Society Admin</div>
            
            @can('is-super-admin')
                {{-- Society --}}
                <x-sidebar-link :href="route('societies.index')" :active="request()->routeIs('societies.index')">
                    <i class="bi bi-buildings-fill"></i>
                    <span class="sidebar-link-label">Society</span>
                </x-sidebar-link>
            @endcan

            @canany(['is-super-admin', 'is-admin'])
                {{-- Wings --}}
                <x-sidebar-link :href="route('wings.index')" :active="request()->routeIs('wings.*')">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span class="sidebar-link-label">Wings</span>
                </x-sidebar-link>
            @endcanany

            @can('is-admin')
                {{-- Flats --}}
                <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.index')">
                    <i class="bi bi-building-fill"></i>
                    <span class="sidebar-link-label">Flats</span>
                </x-sidebar-link>

                {{-- Residents --}}
                <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.index')">
                    <i class="bi bi-people-fill"></i>
                    <span class="sidebar-link-label">Residents</span>
                </x-sidebar-link>

                {{-- Users Management --}}
                <x-sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.index')">
                    <i class="bi bi-person-badge-fill"></i>
                    <span class="sidebar-link-label">Users Management</span>
                </x-sidebar-link>
            @endcan
        @endcanany

        {{-- Section: Visitor Control --}}
        @canany(['is-super-admin', 'is-admin', 'is-resident', 'is-gatekeeper'])
            <div class="sidebar-section-header">Visitor Control</div>

            @canany(['is-admin', 'is-resident'])
                {{-- Visitor Passes --}}
                <x-sidebar-link :href="route('passes.index')" :active="request()->routeIs('passes.*')">
                    <i class="bi bi-person-vcard-fill"></i>
                    <span class="sidebar-link-label">Visitor Passes</span>
                </x-sidebar-link>
            @endcanany

            @canany(['is-admin', 'is-gatekeeper'])
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
            @endcanany
        @endcanany

        {{-- Section: Operations --}}
        <div class="sidebar-section-header">Operations</div>

        {{-- Delivery --}}
        <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
            <i class="bi bi-box-seam-fill"></i>
            <span class="sidebar-link-label">Delivery</span>
        </x-sidebar-link>

        {{-- Complaints --}}
        <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span class="sidebar-link-label">Complaints</span>
        </x-sidebar-link>

        {{-- Activity Logs --}}
        @canany(['is-admin', 'is-super-admin'])
            <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')">
                <i class="bi bi-clock-history"></i>
                <span class="sidebar-link-label">Activity Logs</span>
            </x-sidebar-link>
        @endcanany

        {{-- Section: Analytics --}}
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

    </div>
</aside>
