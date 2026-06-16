<div class="list-group list-group-flush">

    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-dark text-white">
        Dashboard
    </a>

    {{-- ADMIN --}}
    @can('is-admin')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Users
        </a>
        <a href="{{route('flats.index')}}" class="list-group-item list-group-item-action bg-dark text-white">
            flats
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Residents
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Gatekeepers
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Reports
        </a>
    @endcan

    {{-- GATEKEEPER --}}
    @can('is-gatekeeper')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Visitors
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Entry Logs
        </a>
    @endcan
    {{-- RESIDENT --}}
    @can('is-resident')
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            My Visitors
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Complaints
        </a>
    @endcan

</div>
