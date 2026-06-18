<?php

namespace App\Http\Controllers;

use App\Enum\ComplaintCategory;
use App\Enum\ComplaintStatus;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;

use App\Models\Complaint;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    use AuthorizesRequests;

    public function create()
    {
        $this->authorize('create', Complaint::class);
        $category = array_column(ComplaintCategory::cases(), 'value');

        return view('complaints.create', ['categories' => $category]);
    }

    public function store(StoreComplaintRequest $request)
    {
        $this->authorize('create', Complaint::class);

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
        $this->authorize('viewAny', Complaint::class);
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
        $this->authorize('view', $complaint);

        return view('complaints.show', compact('complaint'));
    }

    public function update(UpdateComplaintRequest $request,Complaint $complaint) {
        $this->authorize('update', $complaint);

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
        $this->authorize('update', $complaint);

        return view('complaints.edit', compact('complaint'));
    }
}
