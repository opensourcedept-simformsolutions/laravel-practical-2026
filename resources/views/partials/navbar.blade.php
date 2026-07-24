@php
    $rawModules = [
        // Overview
        ['title' => 'Dashboard Overview', 'category' => 'General', 'icon' => 'bi-speedometer2', 'route' => 'dashboard', 'can' => true],

        // Societies
        ['title' => 'Societies List', 'category' => 'Societies', 'icon' => 'bi-building', 'route' => 'societies.index', 'can' => auth()->user()->can('societies.view')],
        ['title' => 'Add New Society', 'category' => 'Societies', 'icon' => 'bi-plus-circle-fill', 'route' => 'societies.create', 'can' => auth()->user()->can('societies.create')],

        // Wings
        ['title' => 'Wings List', 'category' => 'Wings', 'icon' => 'bi-diagram-3-fill', 'route' => 'wings.index', 'can' => auth()->user()->can('wings.view')],
        ['title' => 'Add New Wing', 'category' => 'Wings', 'icon' => 'bi-plus-circle-fill', 'route' => 'wings.create', 'can' => auth()->user()->can('wings.create')],

        // Flats
        ['title' => 'Flats List', 'category' => 'Flats', 'icon' => 'bi-houses-fill', 'route' => 'flats.index', 'can' => auth()->user()->can('flats.view')],
        ['title' => 'Add New Flat', 'category' => 'Flats', 'icon' => 'bi-plus-circle-fill', 'route' => 'flats.create', 'can' => auth()->user()->can('flats.create')],

        // Residents
        ['title' => 'Residents List', 'category' => 'Residents', 'icon' => 'bi-people-fill', 'route' => 'residents.index', 'can' => auth()->user()->can('residents.view')],
        ['title' => 'Add New Resident', 'category' => 'Residents', 'icon' => 'bi-person-plus-fill', 'route' => 'residents.create', 'can' => auth()->user()->can('residents.create')],

        // Visitor Control
        ['title' => 'Visitor Passes List', 'category' => 'Visitor Control', 'icon' => 'bi-pass-fill', 'route' => 'passes.index', 'can' => auth()->user()->can('passes.view')],
        ['title' => 'Create Visitor Pass', 'category' => 'Visitor Control', 'icon' => 'bi-plus-circle-fill', 'route' => 'passes.create', 'can' => auth()->user()->can('passes.create')],
        ['title' => 'Scan QR Code / Pass', 'category' => 'Visitor Control', 'icon' => 'bi-qr-code-scan', 'route' => 'gatekeeper.scan', 'can' => auth()->user()->can('visitor_logs.view')],

        // Deliveries
        ['title' => 'Deliveries List', 'category' => 'Deliveries', 'icon' => 'bi-box-seam-fill', 'route' => 'deliveries.index', 'can' => auth()->user()->can('deliveries.view')],
        ['title' => 'Log New Delivery', 'category' => 'Deliveries', 'icon' => 'bi-plus-circle-fill', 'route' => 'deliveries.create', 'can' => auth()->user()->can('deliveries.create')],

        // Complaints
        ['title' => 'Complaints List', 'category' => 'Complaints', 'icon' => 'bi-exclamation-triangle-fill', 'route' => 'complaints.index', 'can' => auth()->user()->can('complaints.view')],
        ['title' => 'Lodge New Complaint', 'category' => 'Complaints', 'icon' => 'bi-plus-circle-fill', 'route' => 'complaints.create', 'can' => auth()->user()->can('complaints.create')],

        // User Management
        ['title' => 'User Management', 'category' => 'Administration', 'icon' => 'bi-people', 'route' => 'admin.users.index', 'can' => auth()->user()->can('users.view')],
        ['title' => 'Add New User', 'category' => 'Administration', 'icon' => 'bi-person-plus-fill', 'route' => 'admin.users.create', 'can' => auth()->user()->can('users.create')],

        // Permissions & Security
        ['title' => 'User Permissions Management', 'category' => 'Permissions', 'icon' => 'bi-person-gear', 'route' => 'admin.permissions.index', 'can' => auth()->user()->can('permissions.manage')],
        ['title' => 'Role Default Permissions', 'category' => 'Permissions', 'icon' => 'bi-shield-check', 'route' => 'admin.role-permissions.index', 'can' => auth()->user()->isSuperAdmin()],
        ['title' => 'System Permissions List', 'category' => 'Permissions', 'icon' => 'bi-key-fill', 'route' => 'admin.system-permissions.index', 'can' => auth()->user()->isSuperAdmin()],
        ['title' => 'Create System Permission', 'category' => 'Permissions', 'icon' => 'bi-plus-circle-fill', 'route' => 'admin.system-permissions.create', 'can' => auth()->user()->isSuperAdmin()],

        // System Admin
        ['title' => 'Database Backups & Cloud', 'category' => 'System Admin', 'icon' => 'bi-database-fill-gear', 'route' => 'admin.backups.index', 'can' => auth()->user()->isSuperAdmin()],
        ['title' => 'Activity Logs', 'category' => 'Logs & Audit', 'icon' => 'bi-journal-text', 'route' => 'admin.activity-logs.index', 'can' => auth()->user()->can('activity_logs.view')],
        ['title' => 'Auth Audit Logs', 'category' => 'Logs & Audit', 'icon' => 'bi-shield-lock-fill', 'route' => 'admin.auth-audit.index', 'can' => auth()->user()->can('auth_audit.view')],

        // Account Profile
        ['title' => 'My Profile', 'category' => 'Account', 'icon' => 'bi-person-circle', 'route' => 'profile', 'can' => true],
        ['title' => 'Edit Profile Settings', 'category' => 'Account', 'icon' => 'bi-gear-fill', 'route' => 'profile', 'can' => true],
    ];

    $searchableModules = collect($rawModules)
        ->filter(fn($item) => $item['can'] && Route::has($item['route']))
        ->map(fn($item) => [
            'title' => $item['title'],
            'category' => $item['category'],
            'icon' => $item['icon'],
            'url' => route($item['route']),
        ])
        ->values();
@endphp

<nav class="navbar navbar-expand-lg app-navbar px-3 border-bottom bg-white shadow-xs">
    <div class="container-fluid d-flex align-items-center justify-content-between gap-3">

        <button
            class="btn sidebar-toggle me-2"
            id="sidebarToggle"
            type="button"
            aria-label="Toggle sidebar"
            aria-controls="sidebarContainer"
            aria-expanded="true">
            <i class="bi bi-list"></i>
        </button>

        @if(session()->has('impersonator_id'))
            <div class="impersonation-banner d-flex align-items-center gap-3 me-3">
                <i class="bi bi-person-badge"></i>

                <span>
                    Acting as
                    <strong>{{ auth()->user()->name }}</strong>
                </span>

                <form action="{{ route('impersonate.stop') }}"
                    method="POST"
                    class="m-0">
                    @csrf

                    <button type="submit" class="impersonation-exit-btn">
                        Exit
                    </button>
                </form>
            </div>
        @endif

        {{-- Global Search Bar --}}
        <div class="global-search-wrapper flex-grow-1 mx-3 position-relative" style="max-width: 480px;">
            <div class="input-group input-group-sm rounded-pill border bg-light shadow-2xs overflow-hidden" id="globalSearchGroup">
                <span class="input-group-text border-0 bg-transparent ps-3 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       id="globalSearchInput"
                       class="form-control border-0 bg-transparent px-2 shadow-none text-dark"
                       placeholder="Quick navigate to module or create page..."
                       autocomplete="off"
                       aria-label="Global Search">
                <span class="input-group-text border-0 bg-transparent pe-3">
                    <kbd class="bg-white text-muted border px-2 py-0.5 rounded shadow-2xs fs-9 fw-bold">/</kbd>
                </span>
            </div>

            {{-- Dropdown Search Results --}}
            <div id="globalSearchResults" class="dropdown-menu shadow-lg border-0 w-100 mt-1 py-2 rounded-3" style="display: none; max-height: 380px; overflow-y: auto; z-index: 1060;">
            </div>
        </div>

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

                <ul class="dropdown-menu dropdown-menu-end user-dropdown shadow-sm border-0 mt-2">

                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="bi bi-person"></i> Profile
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

                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>

                </ul>

            </div>
        </div>

    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modules = @json($searchableModules);
    const searchInput = document.getElementById('globalSearchInput');
    const searchResults = document.getElementById('globalSearchResults');
    const searchGroup = document.getElementById('globalSearchGroup');
    let selectedIndex = -1;

    // Press '/' to focus search input from anywhere on page
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
                renderResults(searchInput.value.trim());
            }
        }
    });

    if (!searchInput || !searchResults) return;

    function renderResults(query) {
        const q = query.toLowerCase();
        const filtered = modules.filter(m =>
            m.title.toLowerCase().includes(q) ||
            m.category.toLowerCase().includes(q)
        );

        if (filtered.length === 0) {
            searchResults.innerHTML = '<div class="px-3 py-2 text-muted text-center small"><i class="bi bi-emoji-frown me-1"></i> No matching modules or create pages found</div>';
            searchResults.style.display = 'block';
            selectedIndex = -1;
            return;
        }

        let html = '';
        let currentCat = '';

        filtered.forEach((m, idx) => {
            if (m.category !== currentCat) {
                currentCat = m.category;
                html += `<div class="dropdown-header text-uppercase text-muted fw-bold px-3 pt-2 pb-1 fs-9">${currentCat}</div>`;
            }

            html += `
                <a href="${m.url}" class="dropdown-item global-search-item px-3 py-2 d-flex align-items-center justify-content-between" data-index="${idx}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi ${m.icon} text-primary fs-6"></i>
                        <span class="fw-semibold text-dark fs-8">${m.title}</span>
                    </div>
                    <i class="bi bi-arrow-return-left text-muted fs-9 opacity-50"></i>
                </a>
            `;
        });

        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
        selectedIndex = 0;
        const items = searchResults.querySelectorAll('.global-search-item');
        updateHighlight(items);
    }

    searchInput.addEventListener('input', function() {
        renderResults(this.value.trim());
    });

    searchInput.addEventListener('focus', function() {
        renderResults(this.value.trim());
    });

    // Keyboard Navigation (ArrowUp, ArrowDown, Enter, Escape)
    searchInput.addEventListener('keydown', function(e) {
        const items = searchResults.querySelectorAll('.global-search-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0 && items[selectedIndex]) {
                e.preventDefault();
                const targetUrl = items[selectedIndex].getAttribute('href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            }
        } else if (e.key === 'Escape') {
            searchResults.style.display = 'none';
            searchInput.blur();
        }
    });

    function updateHighlight(items) {
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('active', 'bg-primary', 'bg-opacity-10', 'text-primary', 'fw-bold');
                item.classList.remove('text-dark');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active', 'bg-primary', 'bg-opacity-10', 'text-primary', 'fw-bold');
                item.classList.add('text-dark');
            }
        });
    }

    // Close search dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!searchGroup.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>
