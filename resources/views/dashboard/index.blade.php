@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

  <div class="container-fluid">

    <div class="row g-3 mb-4">

      @foreach ($stats as $key => $value)
        <div class="col-xl-3 col-md-6">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="text-muted text-uppercase">{{ str_replace('_', ' ', $key) }}</h6>
                  <h3 class="mb-0">{{ $value }}</h3>
                </div>
                <i class="bi bi-bar-chart fs-2 text-primary"></i>
              </div>
            </div>
          </div>
        </div>
      @endforeach

    </div>

    @if ($role === 'super_admin')

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header">Latest Societies</div>
            <div class="card-body">
              <ul class="list-group">
                @foreach ($latest_societies as $society)
                  <li class="list-group-item">{{ $society->name }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header">Recent Complaints</div>
            <div class="card-body">
              <ul class="list-group">
                @foreach ($latest_complaints as $c)
                  <li class="list-group-item">{{ $c->category }} - {{ $c->status }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>

    @endif

    @if ($role === 'admin')

      <div class="row">

        <div class="col-md-6">
          <div class="card">
            <div class="card-header">Pending Complaints</div>
            <div class="card-body">
              <ul class="list-group">
                @foreach ($pending_complaints as $c)
                  <li class="list-group-item">{{ $c->category }} - {{ $c->status }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header">Today's Visitors</div>
            <div class="card-body">
              <ul class="list-group">
                @foreach ($today_visitors as $v)
                  <li class="list-group-item">{{ $v->visitor->name ?? 'N/A' }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

      </div>

    @endif

    @if ($role === 'gatekeeper')

      <div class="card">
        <div class="card-header">Active Visitors</div>
        <div class="card-body">
          <ul class="list-group">
            @foreach ($active_visitors as $v)
              <li class="list-group-item">
                {{ $v->visitor->name ?? 'N/A' }} - {{ $v->status }}
              </li>
            @endforeach
          </ul>
        </div>
      </div>

    @endif

    @if ($role === 'resident')

      <div class="row">

        <div class="col-md-4">
          <div class="card">
            <div class="card-header">My Visitors</div>
            <div class="card-body">
              @foreach ($my_visitors as $v)
                <div>{{ $v->visitor->name ?? 'N/A' }}</div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <div class="card-header">My Deliveries</div>
            <div class="card-body">
              @foreach ($my_deliveries as $d)
                <div>{{ $d->vendor ?? 'Vendor' }}</div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <div class="card-header">My Complaints</div>
            <div class="card-body">
              @foreach ($my_complaints as $c)
                <div>{{ $c->category }} - {{ $c->status }}</div>
              @endforeach
            </div>
          </div>
        </div>

      </div>

    @endif

  </div>

@endsection
