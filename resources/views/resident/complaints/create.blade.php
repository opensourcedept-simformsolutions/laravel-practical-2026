<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raise Complaint</title>
</head>

<body>

    <h2>Raise Complaint</h2>

    @if(session('success'))
    <div>
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('resident.complaints.store') }}" method="POST">
        @csrf

        <div>
            <label>Category</label><br>

            <select name="category">
                <option value="">Select Category</option>

                @foreach ($categories as $category)
                <option value="{{ $category }}">
                    {{ $category }}
                </option>
                @endforeach
            </select>

            @error('category')
            <div>
                {{ $message }}
            </div>
            @enderror
        </div>

        <br>

        <div>
            <label>Description</label><br>

            <textarea name="description" rows="5" cols="50"
                placeholder="Enter complaint details...">{{ old('description') }}</textarea>

            @error('description')
            <div>
                {{ $message }}
            </div>
            @enderror
        </div>

        <br>

        <button type="submit">
            Submit Complaint
        </button>
    </form>

</body>

</html>
