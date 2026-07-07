@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
    <div class="container-fluid">
        <div class="row min-vh-100">
            <div class="col-lg-6 d-none d-lg-flex auth-branding align-items-center justify-content-center">
                <div class="text-center">
                    <i class="bi bi-buildings auth-logo"></i>
                    <h1 class="fw-bold mt-3">SocietyMS</h1>
                    <p class="lead">Society Gatekeeper Management System</p>
                </div>
            </div>

            <div class="col-lg-6 d-flex align-items-center justify-content-center p-3">
                <div class="card auth-card shadow w-100" style="max-width:450px;">
                    <div class="card-body p-4">
                        <div class="text-center d-lg-none mb-4">
                            <i class="bi bi-buildings fs-1 text-primary"></i>
                            <h3 class="fw-bold">SocietyMS</h3>
                        </div>

                        <h3 class="text-center mb-4">Verify Email</h3>

                        <div class="mb-4 text-sm text-secondary">
                            {{ __('Thanks for updating your email! Before proceeding, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                        </div>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success mb-4" role="alert">
                                {{ __('A new verification link has been sent to the email address you provided.') }}
                            </div>
                        @endif

                        <div class="mt-4 d-flex flex-column gap-2">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    {{ __('Resend Verification Email') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}" class="w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
