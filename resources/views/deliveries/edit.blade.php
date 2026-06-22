@extends('layouts.app')

@section('title', 'Add Delivery')

@section('content')
    <x-form.form :action="route('deliveries.store')" method="POST" class="mx-auto w-100" style="max-width: 760px;">
        <x-form.form-section title="Add Delivery">
            <x-form.field name="flat_id" label="Flat" required>
                <x-form.select
                    name="flat_id"
                    :options="$flatOptions"
                    :value="old('vendor', $delivery->flat_id)"
                    required />
            </x-form.field>

            <x-form.field name="resident_id" label="Resident" required>
                <x-form.select
                    name="resident_id"
                    :options="$residentOptions"
                    placeholder="Select Resident"
                    :value="old('vendor', $delivery->flat_id)"
                    required />
            </x-form.field>

            <x-form.field name="vendor" label="Vendor" required>
                <x-form.input
                    name="vendor"
                    id="vendor"
                    placeholder="Enter vendor name"
                    :value="old('vendor', $delivery->vendor)"
                    required />
            </x-form.field>

            <x-form.field name="package_details" label="Package Details" required>
                <x-form.textarea
                    name="package_details"
                    id="package_details"
                    rows="4"
                    placeholder="Enter package details"
                    :value="old('package_details', $delivery->package_details)"
                    required />
            </x-form.field>

            <div class="d-flex justify-content-end">
                <x-form.submit-button>
                    Save Delivery
                </x-form.submit-button>
            </div>
        </x-form.form-section>
    </x-form.form>
@endsection
