<div class="sidebar-menu">
    <x-sidebar-link
        :href="route('admin.dashboard')"
        :active="request()->is('admin/dashboard')">
        Dashboard
    </x-sidebar-link>

    @if(auth()->user()->role->name === 'admin')

        <x-sidebar-link href="{{route('admin.users.index') }}" :active="request()->is('/')">
            Users
        </x-sidebar-link>

        <x-sidebar-link href="#" :active="request()->is('/')">
            Residents
        </x-sidebar-link>


    @endif

    @if(auth()->user()->role->name === 'gatekeeper')

        <x-sidebar-link href="#">
            Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Entry Logs
        </x-sidebar-link>

    @endif

    @if(auth()->user()->role->name === 'resident')

        <x-sidebar-link href="#">
            My Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Complaints
        </x-sidebar-link>

    @endif

</div>
