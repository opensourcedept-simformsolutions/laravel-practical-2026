<?php

namespace App\Http\Controllers;

use App\Models\Society;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\StoreSocietyRequest;
use App\Http\Requests\UpdateSocietyRequest;
use Exception;
use Illuminate\Support\Facades\Session;

class SocietyController extends Controller
{
    public function index()
    {
        return view('societies.index');
    }

    public function data()
    {
        $societies = Society::withTrashed();

        return DataTables::of($societies)

            ->addIndexColumn()

            ->editColumn('created_at', function ($society) {
                return $society->created_at->format('d M Y');
            })

            ->addColumn('status', function ($society) {

                return $society->deleted_at
                    ? '<span class="badge bg-danger">Deleted</span>'
                    : '<span class="badge bg-success">Active</span>';
            })

            ->addColumn('actions', function ($society) {

                $actions = '<div class="d-flex justify-content-center gap-2">';

                $actions .= '
                    <a href="' . route('societies.show', $society->id) . '"
                        class="btn btn-info text-white">
                        <i class="bi bi-eye"></i>
                    </a>
                ';

                if (!$society->trashed()) {

                    $actions .= '
                        <a href="' . route('societies.edit', $society->id) . '"
                            class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    ';

                    $actions .= '
                        <button
                            class="btn btn-danger btn-action"
                            data-url="' . route('societies.destroy', $society->id) . '"
                            data-method="DELETE"
                            data-title="Delete Society?"
                            data-text="This action can be restored later."
                            data-confirm="Yes, Delete"
                            data-success="Society deleted successfully">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                } else {

                    $actions .= '
                        <button
                            class="btn btn-success btn-action"
                            data-url="' . route('societies.restore', $society->id) . '"
                            data-method="PATCH"
                            data-title="Restore Society?"
                            data-text="Society will become active again."
                            data-confirm="Yes, Restore"
                            data-success="Society restored successfully">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    ';
                }

                $actions .= '</div>';

                return $actions;
            })

            ->rawColumns(['actions', 'status'])

            ->make(true);
    }

    public function create()
    {
        return view('societies.create');
    }

    public function store(StoreSocietyRequest $request)
    {
        try {

            Society::create($request->validated());

            return redirect()
                ->route('societies.index')
                ->with('success', 'Society created successfully.');
        } catch (Exception $e) {
            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show(string $id)
    {
        $society = Society::withTrashed()
            ->findOrFail($id);

        return view('societies.show', compact('society'));
    }

    public function edit(Society $society)
    {
        return view('societies.edit', compact('society'));
    }


    public function update(UpdateSocietyRequest $request, Society $society)
    {
        try {
            $society->update(
                $request->validated()
            );

            return redirect()
                ->route('societies.index')
                ->with('success', 'Society updated successfully.');
        } catch (Exception $e) {

            Session::flash('message', 'Something went wrong.');
            Session::flash('status', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Society $society)
    {
        try {

            $society->delete();

            return response()->json([
                'success' => true,
                'message' => 'Society deleted successfully.'
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function restore($id)
    {
        try {

            $society = Society::withTrashed()
                ->findOrFail($id);

            $society->restore();

            return response()->json([
                'success' => true,
                'message' => 'Society restored successfully.'
            ]);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
}
