@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">

                            <div class="card-header">
                                <h4 class="mb-0">Profile Information</h4>
                            </div>

                            <div class="card-body">
                                @include('profile.partials.update-profile-information-form')
                            </div>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">

                            <div class="card-header">
                                <h4 class="mb-0">Change Password</h4>
                            </div>

                            <div class="card-body">
                                @include('profile.partials.update-password-form')
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
