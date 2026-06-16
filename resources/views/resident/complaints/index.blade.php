<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints List</title>
</head>

<body>

    <h2>Complaints</h2>

    @if(session('success'))
    <div>
        {{ session('success') }}
    </div>
    <br>
    @endif

    <form method="GET">

        <div>
            <label>Category</label><br>

            <select name="category">
                <option value="">All Categories</option>

                <option value="security" {{ request('category')=='security' ? 'selected' : '' }}>
                    Security
                </option>

                <option value="cleaning" {{ request('category')=='cleaning' ? 'selected' : '' }}>
                    Cleaning
                </option>

                <option value="water" {{ request('category')=='water' ? 'selected' : '' }}>
                    Water
                </option>

                <option value="parking" {{ request('category')=='parking' ? 'selected' : '' }}>
                    Parking
                </option>
            </select>
        </div>

        <br>

        <div>
            <label>Status</label><br>

            <select name="status">
                <option value="">All Status</option>

                <option value="open" {{ request('status')=='open' ? 'selected' : '' }}>
                    Open
                </option>

                <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>
                    In Progress
                </option>

                <option value="resolved" {{ request('status')=='resolved' ? 'selected' : '' }}>
                    Resolved
                </option>
            </select>
        </div>

        <br>

        <div>
            <label>Date</label><br>

            <input type="date" name="date" value="{{ request('date') }}">
        </div>

        <br>

        <button type="submit">
            Filter
        </button>

    </form>

    <hr>

    @if($complaints->count())

    <table border="1" cellpadding="8" cellspacing="0">

        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            @foreach($complaints as $complaint)

            <tr>

                <td>{{ $complaint->id }}</td>

                <td>
                    {{ ucfirst($complaint->category) }}
                </td>

                <td>
                    {{ Str::limit($complaint->description, 50) }}
                </td>

                <td>

                    @if($complaint->status == 'open')
                    Open

                    @elseif($complaint->status == 'in_progress')
                    In Progress

                    @elseif($complaint->status == 'resolved')
                    Resolved
                    @endif

                </td>

                <td>
                    {{ $complaint->created_at->format('d M Y') }}
                </td>

                <td>

                    @if(auth()->user()->role->name === 'admin')

                    <a href="{{ route('admin.complaints.show', $complaint->id) }}">
                        View
                    </a>

                    @else

                    <a href="{{ route('resident.complaints.show', $complaint->id) }}">
                        View
                    </a>

                    @endif

                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <br>

    <div>
        {{ $complaints->appends(request()->query())->links() }}
    </div>

    @else

    <p>No complaints found.</p>

    @endif

</body>

</html>
