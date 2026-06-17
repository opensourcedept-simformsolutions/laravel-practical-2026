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

        return view('complaints.create', ['categories' => $category]);
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
            ->route('complaints.create')
            ->with('success', 'Complaint submitted successfully.');
    }

    public function index(Request $request)
    {
        $query = Complaint::with('user.society');
        $user = auth()->user();
        if (in_array($user->role->name, ['resident', 'gatekeeper'])) {

            $query->where('user_id', $user->id);

        } elseif ($user->role->name === 'admin') {

            $query->whereHas('user', function ($q) use ($user) {
                $q->where('society_id', $user->society_id);
            });

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

        $complaints = $query->latest()->get();

        return view('complaints.index', ['complaints' => $complaints]);
    }

    public function show(Complaint $complaint)
    {
        $user = auth()->user();
        if (in_array($user->role->name, ['resident', 'gatekeeper']) && $complaint->user_id !== $user->id) {
            abort(403);
        }

        if ($user->role->name === 'admin' && $complaint->user->society_id !== $user->society_id) {
            abort(403);
        }

        return view('complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $user = auth()->user();

        if (! in_array($user->role->name, ['admin', 'super_admin'])) {
            abort(403);
        }

        if ($user->role->name === 'admin' && $complaint->user->society_id !== $user->society_id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $complaint->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    public function edit(Complaint $complaint)
    {
        $user = auth()->user();

        if (! in_array($user->role->name, ['admin', 'super_admin'])) {
            abort(403);
        }

        if ($user->role->name === 'admin' && $complaint->user->society_id !== $user->society_id) {
            abort(403);
        }

        return view('complaints.edit', compact('complaint'));
    }
}
