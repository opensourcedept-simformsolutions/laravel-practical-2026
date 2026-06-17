
<div class="sidebar-menu">
    <x-sidebar-link
        :href="route('admin.dashboard')"
        :active="request()->is('admin/dashboard')">

<div class="list-group list-group-flush">

    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-dark text-white">

        Dashboard
    </x-sidebar-link>

    {{-- ADMIN --}}
    @can('is-admin')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">

        <x-sidebar-link href="{{route('admin.users.index') }}" :active="request()->is('/')">
            Users
        </x-sidebar-link>

        <x-sidebar-link href="#" :active="request()->is('/')">
            Residents
        </x-sidebar-link>



    @endcan


    {{-- GATEKEEPER --}}
    @can('is-gatekeeper')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">

            Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Entry Logs

        </x-sidebar-link>


    @endcan
    {{-- RESIDENT --}}
    @can('is-resident')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">

            My Visitors
        </x-sidebar-link>

        <x-sidebar-link href="#">
            Complaints

        </x-sidebar-link>

        </a>
    @endcan

</div>
