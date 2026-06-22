<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <i class="bi bi-buildings"></i>
        <span class="sidebar-brand-text">SocietyMS</span>
    </div>

    <div class="sidebar-menu">

        {{-- Dashboard --}}
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="bi bi-speedometer2"></i>
            <span class="sidebar-link-label">Dashboard</span>
        </x-sidebar-link>

        {{-- Passes --}}
        @cannot(['is-gatekeeper', 'is-admin'])
            <x-sidebar-link :href="route('passes.index')" :active="request()->routeIs('passes.*')">
                <i class="bi bi-pass"></i>
                <span class="sidebar-link-label">Visitor Passes</span>
            </x-sidebar-link>
        @endcannot

        @can('is-gatekeeper')
            <x-sidebar-link :href="route('gatekeeper.visitor-logs.pending')" :active="request()->routeIs('gatekeeper.visitor-logs.*')">
                <i class="bi bi-person-check"></i>
                <span class="sidebar-link-label">Pending Passes</span>
            </x-sidebar-link>
        @endcan

        <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.index')">
            <i class="bi bi-people"></i>
            <span class="sidebar-link-label">Flats</span>
        </x-sidebar-link>

        <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.index')">
            <i class="bi bi-house-door"></i>
            <span class="sidebar-link-label">Residents</span>
        </x-sidebar-link>

        {{-- Complaints --}}
        <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
            <i class="bi bi-exclamation-circle"></i>
            <span class="sidebar-link-label">Complaints</span>
        </x-sidebar-link>

        {{-- Delivery --}}
        <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.index')">
            <i class="bi bi-pass"></i>
            <span class="sidebar-link-label">Delivery</span>
        </x-sidebar-link>

        {{-- Reports --}}
        <div class="sidebar-dropdown">
            <button type="button" class="sidebar-dropdown-toggle" id="reportsToggle">
                <span>
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span class="sidebar-link-label">Reports</span>
                </span>

                <i class="bi bi-chevron-down" id="reportsArrow"></i>
            </button>

            <div class="sidebar-dropdown-menu d-none" id="reportsMenu">

                <x-sidebar-link :href="route('reports.deliveries')">
                    <i class="bi bi-box-seam"></i>
                    <span class="sidebar-link-label">Delivery Report</span>
                </x-sidebar-link>

                <x-sidebar-link :href="route('reports.complaints')">
                    <i class="bi bi-chat-square-text"></i>
                    <span class="sidebar-link-label">Complaint Report</span>
                </x-sidebar-link>

                <x-sidebar-link :href="route('passes.report')">
                    <i class="bi bi-person-vcard"></i>
                    <span class="sidebar-link-label">Visitor Report</span>
                </x-sidebar-link>

            </div>

        </div>

</aside>
