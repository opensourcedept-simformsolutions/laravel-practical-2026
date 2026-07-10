@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark">Edit User</h5>
        </div>

        <form id="userForm" action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body p-4">

                <div class="row g-3">

                    <!-- Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                        <x-form.error name="name" />
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}">
                        <x-form.error name="email" />
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $user->phone) }}">
                        <x-form.error name="phone" />
                    </div>

                    <!-- Role -->
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Role</label>
                        <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <x-form.error name="role_id" />
                    </div>

                    @if (auth()->user()->isSuperAdmin())
                        <div class="col-md-6">
                            <label for="society_id" class="form-label">Society</label>
                            <select id="society_id" name="society_id"
                                class="form-select @error('society_id') is-invalid @enderror">
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
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Leave empty to keep current password">
                        <x-form.error name="password" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>

                </div>

            </div>

            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update User
                </button>
            </div>

        </form>

    </div>

@endsection

