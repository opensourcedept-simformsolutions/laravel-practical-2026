<nav class="navbar navbar-expand-lg app-navbar">

    <div class="container-fluid">

        <button
            class="btn sidebar-toggle"
            id="sidebarToggle"
            type="button"
            aria-label="Toggle sidebar"
            aria-controls="sidebarContainer"
            aria-expanded="true">
            <i class="bi bi-list"></i>
        </button>

        <div class="ms-auto">

            <div class="dropdown">

                <button
                    class="btn nav-user-btn dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                    <i class="bi bi-person-circle user-icon"></i>

                    <div class="user-info">
                        <div class="user-name">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="user-role">
                            {{ ucfirst(auth()->user()->role->name) }}
                        </div>
                    </div>

                </button>

                <ul class="dropdown-menu dropdown-menu-end user-dropdown">

                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="bi bi-person"></i>
                            Profile
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-key"></i>
                            Change Password
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item text-danger">

                                <i class="bi bi-box-arrow-right"></i>
                                Logout

                            </button>
                        </form>
                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>
