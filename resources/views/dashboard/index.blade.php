@extends('layouts.app')

@section('content')

<h1>Welcome {{ auth()->user()->name }}</h1>

@if(auth()->user()->role === 'admin')

    <div class="card">
        Total Residents
    </div>

    <div class="card">
        Total Gatekeepers
    </div>

@endif

@if(auth()->user()->role === 'gatekeeper')

    <div class="card">
        Today's Visitors
    </div>

    <div class="card">
        Pending Entries
    </div>

@endif

@if(auth()->user()->role === 'resident')

    <div class="card">
        My Visitors
    </div>

    <div class="card">
        Complaints
    </div>

@endif

@endsection