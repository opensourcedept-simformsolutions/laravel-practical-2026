@extends('layouts.app')

@section('title', 'Create User')

@section('content')

    <div class="card shadow-sm">

        <div class="card-header">
            <h4 class="mb-0">Create User</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('admin.users.store') }}" method="POST">

                @csrf

                <div class="row g-3">

                    <!-- Name -->
                    <div class="col-md-6">

                        <label for="name" class="form-label">
                            Name
                        </label>

                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">

                        <x-form.error name="name" />

                    </div>

                    <!-- Email -->
                    <div class="col-md-6">

                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">

                        <x-form.error name="email" />

                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">

                        <label for="phone" class="form-label">
                            Phone
                        </label>

                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">

                        <x-form.error name="phone" />

                    </div>

                    <!-- Role -->
                    <div class="col-md-6">

                        <label for="role_id" class="form-label">
                            Role
                        </label>

                        <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror">

                            <option value="">
                                Select Role
                            </option>

                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach

                        </select>

                        <x-form.error name="role_id" />

                    </div>

                    @can('is-superadmin')

                        <div class="col-md-6">
                            <label for="society_id" class="form-label">
                                Society
                            </label>

                            <select id="society_id" name="society_id" class="form-select @error('society_id') is-invalid @enderror">
                                <option value="">
                                    Select Society
                                </option>

                                @foreach ($societies as $society)
                                    <option value="{{ $society->id }}" @selected(old('society_id') == $society->id)>
                                        {{ $society->name }}
                                    </option>
                                @endforeach

                            </select>
                            <x-form.error name="society_id" />

                        </div>

                    @endcan

                    <!-- Password -->
                    <div class="col-md-6">

                        <label for="password" class="form-label">
                            Password
                        </label>

                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror">

                        <x-form.error name="password" />

                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">

                        <label for="password_confirmation" class="form-label">
                            Confirm Password
                        </label>

                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">

                    </div>

                    <div class="col-12 mt-3">

                        <button type="submit" class="btn btn-primary">
                            Create User
                        </button>

                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            Back
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection
