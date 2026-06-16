@extends('layouts.app')

@section('title', 'Create User')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Create User</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.users.store') }}"
              method="POST">

            @csrf

            <div class="mb-3">
                <label>Name</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email') }}">
            </div>

            <div class="mb-3">
                <label>Phone</label>

                <input type="text"
                       name="phone"
                       class="form-control"
                       value="{{ old('phone') }}">
            </div>

            <div class="mb-3">
                <label>Password</label>

                <input type="password"
                       name="password"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Role</label>

                <select name="role_id"
                        class="form-select">

                    <option value="">
                        Select Role
                    </option>

                    @foreach($roles as $role)

                        <option value="{{ $role->id }}">
                            {{ ucfirst($role->name) }}
                        </option>

                    @endforeach

                </select>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                Save User
            </button>

            <a href="{{ route('admin.users.index') }}"
               class="btn btn-secondary">
                Back
            </a>

        </form>

    </div>
</div>

@endsection