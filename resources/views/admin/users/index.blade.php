@extends('layouts.app')

@section('title', 'User Management')

@section('content')

    <div class="d-flex justify-content-between mb-3">
        <h3>User Management</h3>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Create User
        </a>
    </div>

    <x-data-table id="usersTable">

        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th width="180">Action</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($users as $user)

                <tr>
                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>

                    <td>{{ $user->phone }}</td>

                    <td>{{ ucfirst($user->role->name) }}</td>

                    <td>

                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        <form
                            action="{{ route('admin.users.destroy', $user) }}"
                            method="POST"
                            class="d-inline">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this user?')">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </x-data-table>

@endsection