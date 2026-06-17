<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <i class="bi bi-buildings"></i>
        <span class="sidebar-brand-text">SocietyMS</span>
    </div>

    <div class="sidebar-menu">

        @can('is-admin')
            <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                <i class="bi bi-speedometer2"></i>
                <span class="sidebar-link-label">Dashboard</span>
            </x-sidebar-link>

            <x-sidebar-link href="#" :active="request()->routeIs('users.*')">
                <i class="bi bi-people"></i>
                <span class="sidebar-link-label">Users</span>
            </x-sidebar-link>
            <x-sidebar-link :href="route('flats.index')" :active="request()->routeIs('flats.index')">
                <i class="bi bi-people"></i>
                <span class="sidebar-link-label">Flats</span>
            </x-sidebar-link>

            <x-sidebar-link :href="route('residents.index')" :active="request()->routeIs('residents.index')">
                <i class="bi bi-house-door"></i>
                <span class="sidebar-link-label">Residents</span>
            </x-sidebar-link>
        @endcan

        @can('is-gatekeeper')
            <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('gatekeeper.dashboard')">
                <i class="bi bi-speedometer2"></i>
                <span class="sidebar-link-label">Dashboard</span>
            </x-sidebar-link>

            <x-sidebar-link href="#" :active="request()->routeIs('visitors.*')">
                <i class="bi bi-person-check"></i>
                <span class="sidebar-link-label">Visitors</span>
            </x-sidebar-link>

            <x-sidebar-link href="#" :active="request()->routeIs('entry-logs.*')">
                <i class="bi bi-clipboard-check"></i>
                <span class="sidebar-link-label">Entry Logs</span>
            </x-sidebar-link>
        @endcan

        @can('is-resident')
            <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('resident.dashboard')">
                <i class="bi bi-speedometer2"></i>
                <span class="sidebar-link-label">Dashboard</span>
            </x-sidebar-link>

            <x-sidebar-link href="#" :active="request()->routeIs('my-visitors.*')">
                <i class="bi bi-person-badge"></i>
                <span class="sidebar-link-label">My Visitors</span>
            </x-sidebar-link>

            <x-sidebar-link href="#" :active="request()->routeIs('complaints.*')">
                <i class="bi bi-exclamation-circle"></i>
                <span class="sidebar-link-label">Complaints</span>
            </x-sidebar-link>
        @endcan
    </div>
</aside>
