<?php

namespace App\Http\Controllers;

use App\Enum\ComplaintCategory;
use App\Enum\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function create()
    {
        $category = array_column(ComplaintCategory::cases(), 'value');

        return view('resident.complaints.create', ['categories' => $category]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'description' => 'required|min:10|max:1000',
        ]);

        Complaint::create([
            'user_id' => auth()->id(),
            'category' => $request->category,
            'description' => $request->description,
            'status' => ComplaintStatus::OPEN,
        ]);

        return redirect()
            ->route('resident.complaints.create')
            ->with('success', 'Complaint submitted successfully.');
    }

    public function index(Request $request)
    {
        $query = Complaint::query();

        if (auth()->user()->role->name !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $complaints = $query->latest()->paginate(5);

        return view('resident.complaints.index', ['complaints' => $complaints]);
    }

    public function show(Complaint $complaint)
    {
        if (auth()->user()->role->name !== 'admin' && $complaint->user_id !== auth()->id()) {
            abort(403);
        }

        return view('resident.complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        if (auth()->user()->role->name !== 'admin') {
            abort(403);
        }

        $request->validate(['status' => 'required', 'admin_notes' => 'nullable|string|max:1000']);
        $complaint->update(['status' => $request->status, 'admin_notes' => $request->admin_notes]);

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }
}
