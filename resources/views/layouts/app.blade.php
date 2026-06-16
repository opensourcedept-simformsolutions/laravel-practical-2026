<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @include('partials.navbar')

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-0">
                @include('partials.sidebar')
            </div>

            <div class="col-md-9 col-lg-10 content-wrapper">
                @yield('content')
            </div>

        </div>
    </div>


    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')

</body>

</html>
