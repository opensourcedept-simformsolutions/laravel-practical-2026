@extends('layouts.app')

@section('title', 'Create Resident')

@section('content')

<div class="card shadow-sm">

    <div class="card-header">
        <h4 class="mb-0">Create Resident Account</h4>
    </div>

    <div class="card-body">

        <form action="{{ route('residents.store') }}" method="POST">

            @csrf

            <div class="row g-3">

                {{-- Name --}}
                <div class="col-md-6">

                    <label for="name" class="form-label">
                        Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                    >

                    <x-form.error name="name" />

                </div>

                {{-- Email --}}
                <div class="col-md-6">

                    <label for="email" class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                    >

                    <x-form.error name="email" />

                </div>

                {{-- Phone --}}
                <div class="col-md-6">

                    <label for="phone" class="form-label">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                    >

                    <x-form.error name="phone" />

                </div>

                {{-- Flat --}}
                <div class="col-md-6">

                    <label for="flat_id" class="form-label">
                        Flat
                    </label>

                    <select
                        id="flat_id"
                        name="flat_id"
                        class="form-select @error('flat_id') is-invalid @enderror"
                    >

                        <option value="">
                            Select Flat
                        </option>

                        @foreach($flats as $flat)

                            <option
                                value="{{ $flat->id }}"
                                @selected(old('flat_id') == $flat->id)
                            >
                                {{ $flat->wing }} - {{ $flat->flat_number }}
                            </option>

                        @endforeach

                    </select>

                    <x-form.error name="flat_id" />

                </div>

                {{-- Resident Type --}}
                <div class="col-md-6">

                    <label class="form-label d-block">
                        Resident Type
                    </label>

                    <div class="form-check form-check-inline">

                        <input
                            class="form-check-input"
                            type="radio"
                            name="resident_type"
                            value="owner"
                            {{ old('resident_type', 'owner') == 'owner' ? 'checked' : '' }}
                        >

                        <label class="form-check-label">
                            Owner
                        </label>

                    </div>

                    <div class="form-check form-check-inline">

                        <input
                            class="form-check-input"
                            type="radio"
                            name="resident_type"
                            value="tenant"
                            {{ old('resident_type') == 'tenant' ? 'checked' : '' }}
                        >

                        <label class="form-check-label">
                            Tenant
                        </label>

                    </div>

                    <x-form.error name="resident_type" />

                </div>

                <div class="col-12 mt-3">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Create Resident Account
                    </button>

                    <a
                        href="{{ route('residents.index') }}"
                        class="btn btn-secondary"
                    >
                        Back
                    </a>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection