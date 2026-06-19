<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        .table {
            border-spacing: 0 10px !important;
        }

        .table tbody tr {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .table td,
        .table th {
            padding: 14px 16px !important;
            vertical-align: middle;
        }

        .table thead th {
            font-weight: 600;
            background: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
        }

        .table tbody tr:hover {
            background: #f1f5ff !important;
            transition: 0.2s;
        }

        .table .btn {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .dt-length .form-select,
        .dt-search .form-control {
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            box-shadow: none;
        }

        .dt-length .form-select:focus,
        .dt-search .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
        }
    </style>
</head>

<body>

    <div class="app-layout">
        <div class="sidebar-container bg-dark min-vh-100 p-0" id="sidebarContainer">
            @include('partials.sidebar')
        </div>

        <div class="main-content-area">
            @include('partials.navbar')

            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        @if (Session::has('message'))
            Toast.fire({
                icon: "{{ Session::get('status', 'info') }}",
                title: "{{ Session::get('message') }}"
            })
        @endif
    </script>
</body>

</html>
