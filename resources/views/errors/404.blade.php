<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Denied</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #061f2e; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-card {
            background: #fff;
            border-radius: 12px;
            padding: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 900px;
            width: 100%;
        }

        .error-title {
            font-size: 90px;
            font-weight: 700;
            color: #666;
        }

        .error-text {
            color: #666;
        }

        .btn-home {
            border-radius: 8px;
        }

        .illustration img {
            max-width: 100%;
        }
    </style>
</head>

<body>

<div class="error-card">
    <div class="row align-items-center">

        <div class="col-md-6">

            <div class="error-title">404</div>

            <h5 class="fw-bold">Oops! Something went wrong!</h5>

            <p class="error-text">
                The page you are looking for is not available.
            </p>

            <a href="{{ url('/') }}" class="btn btn-dark btn-home">
                Go to Home
            </a>

            <button onclick="history.back()" class="btn btn-outline-secondary btn-home">
                Go Back
            </button>

        </div>

        <div class="col-md-6 text-center illustration">
            <img src="{{asset('images/error.png') }}" />

        </div>

    </div>
</div>

</body>
</html>