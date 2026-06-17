@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div class="mb-4">
    <h2 class="fw-bold">Dashboard</h2>
    <p class="text-muted mb-0">
        Welcome back, {{ auth()->user()->name }}
    </p>
</div>

<div class="row g-4">

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted mb-2">
                    Total Residents
                </h6>

                <h2 class="fw-bold mb-0">
                    0
                </h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted mb-2">
                    Total Gatekeepers
                </h6>

                <h2 class="fw-bold mb-0">
                    0
                </h2>
            </div>
        </div>
    </div>

</div>

@endsection
