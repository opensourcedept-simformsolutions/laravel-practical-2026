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
        <x-sidebar-link :href="route('passes.index')" :active="request()->routeIs('passes.index')">
            <i class="bi bi-pass"></i>
            <span class="sidebar-link-label">Visitor Passes</span>
        </x-sidebar-link>

        <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.index')">
            <i class="bi bi-pass"></i>
            <span class="sidebar-link-label">Delivery</span>
        </x-sidebar-link>

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

                <x-sidebar-link :href="route('reports.passes')">
                    <i class="bi bi-person-vcard"></i>
                    <span class="sidebar-link-label">Visitor Report</span>
                </x-sidebar-link>

            </div>
        </div>

    </div>
</aside>

{{-- =========================
             SUPER ADMIN
        ========================== --}}

{{-- @can('manage-societies')
      <x-sidebar-link :href="route('societies.index')" :active="request()->routeIs('societies.*')">
        <i class="bi bi-buildings"></i>
        <span class="sidebar-link-label">Societies</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('manage-admins')
      <x-sidebar-link :href="route('admins.index')" :active="request()->routeIs('admins.*')">
        <i class="bi bi-person-badge"></i>
        <span class="sidebar-link-label">Admins</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             ADMIN + SUPER ADMIN
        ========================== --}}

{{-- @can('manage-users')
      <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')">
        <i class="bi bi-people"></i>
        <span class="sidebar-link-label">Users</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('manage-gatekeepers')
      <x-sidebar-link :href="route('gatekeepers.index')" :active="request()->routeIs('gatekeepers.*')">
        <i class="bi bi-shield-check"></i>
        <span class="sidebar-link-label">Gatekeepers</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('manage-residents')
      <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.*')">
        <i class="bi bi-house-door"></i>
        <span class="sidebar-link-label">Residents</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('manage-flats')
      <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.*')">
        <i class="bi bi-grid-3x3-gap"></i>
        <span class="sidebar-link-label">Flats</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             VISITORS
        ========================== --}}

{{-- @can('manage-visitors')
      <x-sidebar-link :href="route('visitors.index')" :active="request()->routeIs('visitors.*')">
        <i class="bi bi-person-check"></i>
        <span class="sidebar-link-label">Visitors</span>
      </x-sidebar-link>

      <x-sidebar-link :href="route('visitor-logs.index')" :active="request()->routeIs('visitor-logs.*')">
        <i class="bi bi-clipboard-check"></i>
        <span class="sidebar-link-label">Visitor Logs</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('manage-own-visitors')
      <x-sidebar-link :href="route('my-visitors.index')" :active="request()->routeIs('my-visitors.*')">
        <i class="bi bi-person-badge"></i>
        <span class="sidebar-link-label">My Visitors</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             DELIVERIES
        ========================== --}}

{{-- @can('manage-deliveries')
      <x-sidebar-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
        <i class="bi bi-box-seam"></i>
        <span class="sidebar-link-label">Deliveries</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('view-own-deliveries')
      <x-sidebar-link :href="route('my-deliveries.index')" :active="request()->routeIs('my-deliveries.*')">
        <i class="bi bi-box"></i>
        <span class="sidebar-link-label">My Deliveries</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             COMPLAINTS
        ========================== --}}

{{-- @can('manage-complaints')
      <x-sidebar-link :href="route('complaints.index')" :active="request()->routeIs('complaints.*')">
        <i class="bi bi-exclamation-triangle"></i>
        <span class="sidebar-link-label">Complaints</span>
      </x-sidebar-link>
    @endcan --}}

{{-- @can('create-complaints')
      <x-sidebar-link :href="route('my-complaints.index')" :active="request()->routeIs('my-complaints.*')">
        <i class="bi bi-chat-left-text"></i>
        <span class="sidebar-link-label">My Complaints</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             REPORTS
        ========================== --}}

{{-- @can('view-reports')
      <x-sidebar-link :href="route('reports.visitors')" :active="request()->routeIs('reports.*')">
        <i class="bi bi-graph-up"></i>
        <span class="sidebar-link-label">Reports</span>
      </x-sidebar-link>
    @endcan --}}

{{-- =========================
             SOCIETY DETAILS
        ========================== --}}

{{-- @can('view-society')
      <x-sidebar-link :href="route('society.show')" :active="request()->routeIs('society.*')">
        <i class="bi bi-building"></i>
        <span class="sidebar-link-label">Society Details</span>
      </x-sidebar-link>
    @endcan --}}
