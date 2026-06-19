<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

    <div class="container-fluid">

        <a class="navbar-brand" href="#">
            Society System
        </a>

        <div class="d-flex align-items-center">

            <span class="text-white me-3">
                {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-light btn-sm">
                    Logout
                </button>
            </form>

        </div>

    </div>

</nav>
