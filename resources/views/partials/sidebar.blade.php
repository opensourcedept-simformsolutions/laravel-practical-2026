<div class="list-group list-group-flush">

    <a href="{{ route('admin.dashboard') }}"
       class="list-group-item list-group-item-action bg-dark text-white">
        Dashboard
    </a>

    {{-- ADMIN --}}
    @if(auth()->user()->role->name === 'admin')

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Users
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

    @endif

    {{-- GATEKEEPER --}}
    @if(auth()->user()->role->name === 'gatekeeper')

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Visitors
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Entry Logs
        </a>

    @endif

    {{-- RESIDENT --}}
    @if(auth()->user()->role->name === 'resident')

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            My Visitors
        </a>

        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            Complaints
        </a>

    @endif

</div>