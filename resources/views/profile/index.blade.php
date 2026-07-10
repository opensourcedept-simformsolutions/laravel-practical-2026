@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

    <div class="container-fluid">

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <b class="mb-1 h6   ">{{ $user->name }}</b>

                        <span class="badge bg-primary">
                            {{ ucfirst($user->role->name) }}
                        </span>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">

                        <i class="bi bi-pencil-square"></i>
                        Edit Profile

                    </a>

                </div>

                <hr>
                <br>

                <div class="row">

                    <div class="col-md-6">

                        <h5 class="mb-3">Personal Information</h5>

                        <table class="table table-borderless">

                            <tr>
                                <th width="180">Full Name</th>
                                <td>{{ $user->name }}</td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>

                            <tr>
                                <th>Phone</th>
                                <td>{{ $user->phone }}</td>
                            </tr>

                            <tr>
                                <th>Role</th>
                                <td>{{ ucfirst($user->role->name) }}</td>
                            </tr>

                            <tr>
                                <th>Member Since</th>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                            </tr>

                        </table>

                    </div>

                    @can('is-resident')
                        <div class="col-md-6">

                            <h5 class="mb-3">Residence Information</h5>

                            <table class="table table-borderless">

                                <tr>
                                    <th width="180">Resident Type</th>
                                    <td>
                                        {{ ucfirst($user->resident->resident_type ?? '-') }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Wing</th>
                                    <td>
                                        {{ $user->resident?->flat?->wing ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Floor</th>
                                    <td>
                                        {{ $user->resident?->flat?->floor ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Flat Number</th>
                                    <td>
                                        {{ $user->resident?->flat?->flat_number ?? '-' }}
                                    </td>
                                </tr>
                                

                            </table>

                        </div>
                    @endcan

                </div>

            </div>

        </div>

    </div>

@endsection
