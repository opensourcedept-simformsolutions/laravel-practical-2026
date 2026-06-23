<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\Request;

class GateKeeperController extends Controller
{
    public function scanPage()
    {
        return view('gatekeeper.scan');
    }

    public function findPass(Request $request)
    {
        $visitorLogId = decrypt($request->qr_code);

        $visitorLog = VisitorLog::with([
            'visitor',
            'flat',
        ])->findOrFail($visitorLogId);

        return response()->json([
            'id' => $visitorLog->id,
            'visitor' => $visitorLog->visitor->name,
            'phone' => $visitorLog->visitor->phone,
            'purpose' => $visitorLog->purpose,
            'status' => $visitorLog->status,
            'flat' => $visitorLog->flat->wing.'-'.$visitorLog->flat->flat_number,
        ]);
    }

public function markEntry(Request $request, VisitorLog $visitorLog)
{
    $request->validate([
        'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
    ]);

    if ($visitorLog->status !== 'pending') {

        return response()->json([
            'success' => false,
            'message' => 'Visitor already entered or pass not valid.',
        ], 422);
    }

    $visitorLog->status = 'entered';
    $visitorLog->entry_time = now();
    $visitorLog->gatekeeper_id = auth()->id();

    $visitorLog->photo_path = $request
        ->file('photo')
        ->store('visitor_photos', 'public');

    $visitorLog->save();

    return response()->json([
        'success' => true,
        'message' => 'Entry marked successfully.',
    ]);
}
}
