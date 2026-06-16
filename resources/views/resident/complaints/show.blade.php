<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Details</title>
</head>

<body>

    <div>

        <div>
            @if(auth()->user()->role->name === 'admin')
            <a href="{{ route('admin.complaints.index') }}">
                Back
            </a>
            @else
            <a href="{{ route('resident.complaints.index') }}">
                Back
            </a>
            @endif
        </div>

        <br>

        @if(session('success'))
        <div>
            {{ session('success') }}
        </div>
        <br>
        @endif

        <div>

            <h4>
                Complaint #{{ $complaint->id }}
            </h4>

            <hr>

            <div>
                <strong>Category:</strong>
                {{ ucfirst($complaint->category) }}
            </div>

            <br>

            <div>
                <strong>Description:</strong>
                <br>
                {{ $complaint->description }}
            </div>

            <br>

            <div>
                <strong>Status:</strong>

                @if($complaint->status == 'open')
                Open
                @elseif($complaint->status == 'in_progress')
                In Progress
                @else
                Resolved
                @endif
            </div>

            <br>

            <div>
                <strong>Created At:</strong>
                {{ $complaint->created_at->format('d M Y h:i A') }}
            </div>

            <br>

            <div>
                <strong>Admin Notes:</strong>

                <div>
                    {{ $complaint->admin_notes ?? 'No notes added yet.' }}
                </div>
            </div>

        </div>

        @if(auth()->user()->role->name === 'admin')

        <hr>

        <div>

            <h5>
                Update Complaint
            </h5>

            <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">

                @csrf
                @method('PATCH')

                <div>

                    <label>
                        Status
                    </label>

                    <br>

                    <select name="status">

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

                </div>

                <br>

                <div>

                    <label>
                        Admin Notes
                    </label>

                    <br>

                    <textarea name="admin_notes" rows="5"
                        cols="50">{{ old('admin_notes', $complaint->admin_notes) }}</textarea>

                </div>

                <br>

                <button type="submit">
                    Update Complaint
                </button>

            </form>

        </div>

        @endif

    </div>

</body>

</html>
