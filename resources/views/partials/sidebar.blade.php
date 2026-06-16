<div class="sidebar-menu">
    <x-sidebar-link
        :href="route('admin.dashboard')"
        :active="request()->is('admin/dashboard')">
        Dashboard
    </x-sidebar-link>

    @if(auth()->user()->role->name === 'admin')

        <x-sidebar-link href="#" :active="request()->is('/')">
            Users
        </x-sidebar-link>

        <x-sidebar-link href="#" :active="request()->is('/')">
            Residents
        </x-sidebar-link>


    @endif

    {{-- GATEKEEPER --}}
    @if(auth()->user()->role->name === 'gatekeeper')

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Entry Logs
        </a>

    @endif

    {{-- RESIDENT --}}
    @if(auth()->user()->role->name === 'resident')

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            My Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Complaints
        </a>

    @endif

</div>
