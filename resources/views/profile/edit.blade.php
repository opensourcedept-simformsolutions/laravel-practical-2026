@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-person-fill text-primary me-2"></i>Edit Profile Information
                        </h5>
                        <a href="{{ route('profile') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="card-body p-4">
                            @if (session('status') === 'profile-updated')
                                <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>Profile updated successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('status') === 'verification-link-sent')
                                <div class="alert alert-warning alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>A verification link has been sent to your new email address. Please check your inbox.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold text-secondary small">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                                    class="form-control @error('name') is-invalid @enderror" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-semibold text-secondary small">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" 
                                    class="form-control @error('phone') is-invalid @enderror" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-secondary small">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                                    class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-2 text-danger small">
                                        Your email address is unverified.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                            <a href="{{ route('profile') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
