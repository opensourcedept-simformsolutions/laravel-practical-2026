<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title> @yield('title', 'Dashboard') </title>

  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
  <!-- Bootstrap 5 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- DataTables Bootstrap 5 CSS -->
  <link href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css" rel="stylesheet">

  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Select2 Bootstrap 5 Theme -->
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet">

  <!-- daterangepicker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    .form-select,
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

    .impersonation-banner {
      background: #fff3cd;
      color: #664d03;
      border: 1px solid #ffecb5;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 14px;
    }

    .impersonation-exit-btn {
      background: transparent;
      border: none;
      padding: 0;
      color: inherit;
      text-decoration: underline;
      font-weight: 600;
      cursor: pointer;
    }

    .impersonation-exit-btn:hover {
      opacity: 0.8;
    }
  </style>
</head>


<body class="bg-light">

  <div class="app-layout">
    <div class="sidebar-container bg-dark min-vh-100 p-0" id="sidebarContainer">
      @include('partials.sidebar')
    </div>
    <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>

    <div class="main-content-area">
      @include('partials.navbar')

      <div class="content-wrapper">
        @yield('content')
      </div>
    </div>
  </div>

  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>

  <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>

  <!-- for PDF + Excel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- client side jqury validations 1.20 version -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/additional-methods.min.js"></script>

  <!-- Date-Range Picker -->
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <!-- QR Code -->
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

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
      timerProgressBar: true,
    });
  </script>

  @stack('scripts')

  <script>
    $(document).on('click', '.btn-action', function() {

      let btn = $(this);

      let url = btn.data('url');
      let method = btn.data('method') || 'POST';

      let title = btn.data('title') || 'Are you sure?';
      let text = btn.data('text') || '';
      let confirmText = btn.data('confirm') || 'Yes';
      let successText = btn.data('success') || 'Success';

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
            let message = 'Something went wrong.';
            if (xhr.responseJSON?.message) {
              message = xhr.responseJSON.message;
            }
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
      })
    @endif
  </script>

</body>

</html>
