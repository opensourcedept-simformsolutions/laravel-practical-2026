@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    <div class="container-fluid">

        <div class="row min-vh-100">

            <div
                class="col-lg-6 d-none d-lg-flex auth-branding
                    align-items-center justify-content-center">

                <div class="text-center">

                    <i class="bi bi-buildings auth-logo"></i>

                    <h1 class="fw-bold mt-3">
                        SocietyMS
                    </h1>

                    <p class="lead">
                        Society Gatekeeper Management System
                    </p>

                </div>

            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-3">

                <div class="card auth-card shadow w-100" style="max-width:450px;">

                    <div class="card-body p-4">

                        <div class="text-center d-lg-none mb-4">

                            <i class="bi bi-buildings fs-1 text-primary"></i>

                            <h3 class="fw-bold">
                                SocietyMS
                            </h3>

                        </div>

                        <h3 class="text-center mb-4">
                            Login
                        </h3>

                        <form method="POST" action="{{ route('login') }}">

                            @csrf

                            <div class="mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror">

                                <x-form.error name="email" />

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <input type="password" name="password" autocomplete="off"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror">

                                <x-form.error name="password" />

                            </div>

                            <div class="form-check mb-3">

                                <input type="checkbox" name="remember" id="remember" value="1" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>

                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">

                                Login

                            </button>

                            <div class="text-center mt-3">

                                <a href="{{ route('password.request') }}">
                                    Forgot Password?
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
