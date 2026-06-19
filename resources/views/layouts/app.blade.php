<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>

    <!-- Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css" rel="stylesheet">

    <!-- Buttons -->
    <link href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.bootstrap5.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .table {
            border-spacing: 0 10px !important;
        }

        .table tbody tr {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
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
            transition: .2s;
        }

        .table .btn {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 6px;
        }
    </style>
</head>

<body class="bg-light">

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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>

    <!-- Buttons -->
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>

    <!-- Excel Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var table = null;

        function rd() {
            if (table) {
                table.ajax.reload(null, false);
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>

    @stack('scripts')

    @yield('scripts')

    <script>
        $(document).on('click', '.btn-action', function() {

            let btn = $(this);

            let url = btn.data('url');
            let method = btn.data('method') || 'POST';

            let title = btn.data('title') || 'Are you sure?';
            let text = btn.data('text') || '';

            let confirmText =
                btn.data('confirm') || 'Yes';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmText,
                confirmButtonColor: '#dc3545'
            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: url,
                    type: method,

                    success: function(response) {

                        let successText = btn.data('success');

                        Toast.fire({
                            icon: 'success',
                            title: successText || response.message || 'Success'
                        });

                        rd();
                    },

                    error: function(xhr) {

                        let message =
                            xhr.responseJSON?.message ||
                            'Something went wrong';

                        Toast.fire({
                            icon: 'error',
                            title: message
                        });
                    }
                });

            });

        });

        @if (Session::has('message'))

            Toast.fire({
                icon: "{{ Session::get('status', 'info') }}",
                title: "{{ Session::get('message') }}"
            });
        @endif
    </script>

</body>

</html>
