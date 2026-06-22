@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

    <div class="container-fluid">

        <div class="row min-vh-100">

            <div
                class="col-lg-6 d-none d-lg-flex auth-branding
                    align-items-center justify-content-center">

                <div class="text-center">

                    <i class="bi bi-key-fill auth-logo"></i>

                    <h1 class="fw-bold mt-3">
                        SocietyMS
                    </h1>

                    <p class="lead">
                        Create a new password
                    </p>

                </div>

            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-3">

                <div class="card auth-card shadow w-100" style="max-width:450px;">
                    <div class="text-center d-lg-none mb-4">

                        <i class="bi bi-buildings fs-1 text-primary"></i>

                        <h3 class="fw-bold">
                            SocietyMS
                        </h3>

                    </div>
                    <div class="card-body p-4">

                        <h3 class="text-center mb-4">
                            Reset Password
                        </h3>

                        <form method="POST" action="{{ route('password.store') }}">

                            @csrf

                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="mb-3">

                                <label>Email</label>

                                <input type="email" name="email" value="{{ old('email', $request->email) }}"
                                    class="form-control form-control-lg">

                            </div>

                            <div class="mb-3">

                                <label>New Password</label>

                                <input type="password" name="password" class="form-control form-control-lg">

                                <x-form.error name="password" />

                            </div>

                            <div class="mb-3">

                                <label>Confirm Password</label>

                                <input type="password" name="password_confirmation" class="form-control form-control-lg">

                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">

                                Update Password

                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <a href="{{ route('login') }}">
                                Back To Login
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
