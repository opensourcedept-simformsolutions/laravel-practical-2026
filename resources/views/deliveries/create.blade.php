@extends('layouts.app')

@section('title', 'Add Delivery')

@section('content')
    <x-form.form :action="route('deliveries.store')" method="POST" class="mx-auto w-100" style="max-width: 760px;">
        <x-form.form-section title="Add Delivery">
            <x-form.field name="flat_id" label="Flat">
                <x-form.select
                    name="flat_id"
                    :options="$flatOptions"
                    placeholder="Select Flat Number"
                 />
            </x-form.field>

            <x-form.field name="resident_id" label="Resident">
                <x-form.select
                    name="resident_id"
                    :options="$residentOptions"
                    placeholder="Select Resident"
                 />
            </x-form.field>

            <x-form.field name="vendor" label="Vendor">
                <x-form.input
                    name="vendor"
                    id="vendor"
                    placeholder="Enter vendor name"
                 />
            </x-form.field>

            <x-form.field name="package_details" label="Package Details">
                <x-form.textarea
                    name="package_details"
                    id="package_details" rows="4"
                    placeholder="Enter package details"
                 />
            </x-form.field>

            <div class="d-flex justify-content-end">
                <x-form.submit-button>
                    Save Delivery
                </x-form.submit-button>
            </div>
        </x-form.form-section>
    </x-form.form>
@endsection
