@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Edit User</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('admin.users.update', $user) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Name</label>

                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name', $user->name) }}">
            </div>

            <div class="mb-3">
                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       value="{{ old('email', $user->email) }}">
            </div>

            <div class="mb-3">
                <label>Phone</label>

                <input type="text"
                       name="phone"
                       class="form-control"
                       value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="mb-3">
                <label>Role</label>

                <select name="role_id"
                        class="form-select">

                    @foreach($roles as $role)

                        <option
                            value="{{ $role->id }}"
                            {{ $user->role_id == $role->id ? 'selected' : '' }}>

                            {{ ucfirst($role->name) }}

                        </option>

                    @endforeach

                </select>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                Update User
            </button>

            <a href="{{ route('admin.users.index') }}"
               class="btn btn-secondary">
                Back
            </a>

        </form>

    </div>
</div>

@endsection