@extends('layouts.app')

@section('title', 'Edit Complaint')

@section('content')

<x-form.form :action="route('admin.complaints.update', $complaint)" method="POST" class="form-contained">

    @method('PATCH')

    <x-form.form-section title="Update Complaint">

        <x-form.fieldset legend="Complaint Status">

            <x-form.field name="status" label="Status" required>

                <select name="status" id="status" class="form-control">

                    <option value="open" {{ $complaint->status == 'open' ? 'selected' : '' }}>
                        Open
                    </option>

                    <option value="in_progress" {{ $complaint->status == 'in_progress' ? 'selected' : '' }}>
                        In Progress
                    </option>

                    <option value="resolved" {{ $complaint->status == 'resolved' ? 'selected' : '' }}>
                        Resolved
                    </option>

                </select>

            </x-form.field>

            <x-form.field name="admin_notes" label="Admin Notes">

                <textarea name="admin_notes" id="admin_notes" rows="5"
                    class="form-control">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>

            </x-form.field>

            <div class="d-flex justify-content-end">

                <x-form.submit-button>
                    Update Complaint
                </x-form.submit-button>

            </div>

        </x-form.fieldset>

    </x-form.form-section>

</x-form.form>

@endsection
