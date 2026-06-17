@extends('layouts.app')

@section('title', 'Raise Complaint')

@section('content')

<x-form.form :action="route('complaints.store')" method="POST" class="form-contained">


    <x-form.form-section title="Raise Complaint">

        <x-form.fieldset legend="Complaint Details">

            <x-form.field name="category" label="Category" required>

                <select name="category" id="category" class="form-control">

                    <option value="">Select Category</option>

                    @foreach($categories as $category)
                    <option value="{{ $category }}" {{ old('category')==$category ? 'selected' : '' }}>
                        {{ ucfirst($category) }}
                    </option>
                    @endforeach

                </select>

                <x-form.error name="category" />

            </x-form.field>

            <x-form.field name="description" label="Description" required>

                <textarea name="description" id="description" rows="5" class="form-control"
                    placeholder="Enter complaint details...">{{ old('description') }}</textarea>

                <x-form.error name="description" />

            </x-form.field>

            <div class="d-flex justify-content-between align-items-center mb-4">

                <a href="{{ route('complaints.index') }}" class="btn btn-outline-primary">
                    My Complaints
                </a>

                <div class="d-flex justify-content-end">

                    <x-form.submit-button>
                        Submit Complaint
                    </x-form.submit-button>

                </div>
            </div>

        </x-form.fieldset>

    </x-form.form-section>


</x-form.form>

@endsection
