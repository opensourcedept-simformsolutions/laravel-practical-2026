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

    public function markEntry(VisitorLog $visitorLog)
    {
        if ($visitorLog->status !== 'pending') {

            return response()->json([
                'success' => false,
                'message' => 'Visitor already entered or pass not valid.',
            ], 422);
        }

        $visitorLog->update([
            'status' => 'entered',
            'entry_time' => now(),
            'gatekeeper_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Entry marked successfully.',
        ]);
    }
}
