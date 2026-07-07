@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-key-fill text-primary me-2"></i>Change Password
                        </h5>
                        <a href="{{ route('profile') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="card-body p-4">
                            @if (session('status') === 'password-updated')
                                <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>Password changed successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($errors->updatePassword->any())
                                <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-exclamation-octagon-fill me-2"></i>Please correct the errors below.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold text-secondary small">Current Password</label>
                                <input type="password" name="current_password" id="current_password" 
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required autocomplete="current-password">
                                @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold text-secondary small">New Password</label>
                                <input type="password" name="password" id="password" 
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                                @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold text-secondary small">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" required autocomplete="new-password">
                                @error('password_confirmation', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                            <a href="{{ route('profile') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
