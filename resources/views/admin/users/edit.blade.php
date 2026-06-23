@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Edit User</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <!-- Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control class="form-control
                            @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                    </div>
                    <x-form.error name="name" />


                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control class="form-control
                            @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                    </div>
                    <x-form.error name="email" />


                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $user->phone) }}">
                    </div>
                    <x-form.error name="phone" />


                    <!-- Role -->
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Role</label>
                        <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-form.error name="role_id" />

                    @if (auth()->user()->isSuperAdmin())

                        <div class="col-md-6">
                            <label for="society_id" class="form-label">
                                Society
                            </label>

                            <select id="society_id" name="society_id" class="form-select">

                                @foreach ($societies as $society)
                                    <option value="{{ $society->id }}" @selected(old('society_id', $user->society_id) == $society->id)>
                                        {{ $society->name }}
                                    </option>
                                @endforeach

                            </select>
                            <x-form.error name="society_id" />

                        </div>

                    @endif

                    <!-- New Password -->
                    <div class="col-lg-6">
                        <label for="password" class="form-label">
                            New Password
                        </label>
                        <input type="password" id="password" name="password" class="form-control  @error('password') is-invalid @enderror"
                            placeholder="Leave empty to keep current password">
                    </div>
                    <x-form.error name="password" />


                    <!-- Confirm Password -->
                    <div class="col-lg-6">
                        <label for="password_confirmation" class="form-label">
                            Confirm Password
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Update User
                        </button>

                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection
