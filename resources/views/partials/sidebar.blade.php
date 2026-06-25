<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-buildings text-primary"></i>
            <span class="sidebar-brand-text">SocietyMS</span>
        </div>
        <button type="button" class="btn text-white p-0 d-lg-none" id="sidebarClose" aria-label="Close sidebar">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <div class="sidebar-menu">

        {{-- Dashboard --}}
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="bi bi-speedometer2"></i>
            <span class="sidebar-link-label">Dashboard</span>
        </x-sidebar-link>

        @can('is-super-admin')
            {{-- Society --}}
            <x-sidebar-link :href="route('societies.index')" :active="request()->routeIs('societies.index')">
                <i class="bi bi-buildings"></i>
                <span class="sidebar-link-label">Society</span>
            </x-sidebar-link>
        @endcan

        @can('is-admin')
            {{-- Flats --}}
            <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.index')">
                <i class="bi bi-building"></i>
                <span class="sidebar-link-label">Flats</span>
            </x-sidebar-link>

            {{-- Residents --}}
            <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.index')">
                <i class="bi bi-people-fill"></i>
                <span class="sidebar-link-label">Residents</span>
            </x-sidebar-link>

            {{-- Users Management --}}
            <x-sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.index')">
                <i class="bi bi-person-gear"></i>
                <span class="sidebar-link-label">Users Management</span>
            </x-sidebar-link>
        @endcan

        {{-- Visitor Passes --}}
        @canany(['is-admin', 'is-resident'])
            <x-sidebar-link :href="route('passes.index')" :active="request()->routeIs('passes.*')">
                <i class="bi bi-person-vcard"></i>
                <span class="sidebar-link-label">Visitor Passes</span>
            </x-sidebar-link>
        @endcanany

        @canany(['is-admin', 'is-gatekeeper'])
            {{-- Pending Passes --}}
            <x-sidebar-link :href="route('gatekeeper.visitor-logs.pending')" :active="request()->routeIs('gatekeeper.visitor-logs.*')">
                <i class="bi bi-person-check"></i>
                <span class="sidebar-link-label">Pending Passes</span>
            </x-sidebar-link>

            {{-- Scan Visitor Pass --}}
            <x-sidebar-link :href="route('gatekeeper.scan')" :active="request()->routeIs('gatekeeper.scan')">
                <i class="bi bi-qr-code-scan"></i>
                <span class="sidebar-link-label">Scan Visitor Pass</span>
            </x-sidebar-link>
        @endcanany

        {{-- Delivery --}}
        <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
            <i class="bi bi-truck"></i>
            <span class="sidebar-link-label">Delivery</span>
        </x-sidebar-link>

        {{-- Complaints --}}
        <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
            <i class="bi bi-exclamation-circle"></i>
            <span class="sidebar-link-label">Complaints</span>
        </x-sidebar-link>

        {{-- Activity Logs --}}
        @canany(['is-admin', 'is-super-admin'])
            <x-sidebar-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.*')">
                <i class="bi bi-journal-text"></i>
                <span class="sidebar-link-label">Activity Logs</span>
            </x-sidebar-link>
        @endcanany

        {{-- Reports --}}
        @php
            $reportsActive = request()->routeIs('reports.*');
        @endphp
        <div class="sidebar-dropdown">
            <button type="button" class="sidebar-dropdown-toggle" id="reportsToggle">
                <span>
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span class="sidebar-link-label">Reports</span>
                </span>

                <i class="bi bi-chevron-down {{ $reportsActive ? 'rotate-180' : '' }}" id="reportsArrow"></i>
            </button>

            <div class="sidebar-dropdown-menu {{ $reportsActive ? '' : 'd-none' }}" id="reportsMenu">

                <x-sidebar-link :href="route('reports.deliveries.')" :active="request()->routeIs('reports.deliveries*')">
                    <i class="bi bi-box-seam"></i>
                    <span class="sidebar-link-label">Delivery Report</span>
                </x-sidebar-link>

                <x-sidebar-link :href="route('reports.complaints.')" :active="request()->routeIs('reports.complaints*')">
                    <i class="bi bi-chat-square-text"></i>
                    <span class="sidebar-link-label">Complaint Report</span>
                </x-sidebar-link>

                <x-sidebar-link :href="route('reports.passes.')" :active="request()->routeIs('reports.passes*')">
                    <i class="bi bi-person-vcard"></i>
                    <span class="sidebar-link-label">Visitor Report</span>
                </x-sidebar-link>

            </div>

        </div>

    </div>
</aside>
