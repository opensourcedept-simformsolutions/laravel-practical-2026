<?php

namespace App\Http\Controllers;

use App\Events\VisitorEntered;
use App\Models\VisitorLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        if ($visitorLog->status !== 'pending') {

            return response()->json([
                'success' => false,
                'message' => 'Visitor already entered or pass not valid.',
            ], 422);
        }

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
        try {
            $request->validate([
                'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ]);

            $visitorLog->status = 'entered';
            $visitorLog->entry_time = now();
            $visitorLog->gatekeeper_id = auth()->id();

            $visitorLog->photo_path = $request
                ->file('photo')
                ->store('visitor_photos', 'public');

            $visitorLog->save();

            Log::info('VisitorEntered event dispatching from GateKeeper', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            event(
                new VisitorEntered($visitorLog)
            );

            Log::info('VisitorEntered event dispatched from GateKeeper', [
                'visitor_log_id' => $visitorLog->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Entry marked successfully.',
            ]);
        } catch (Exception $e) {

            Log::error('Visitor Entry Error from GateKeeper: '.$e->getMessage(), [
                'visitor_log_id' => $visitorLog->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong While Mark Entry',
            ], 500);
        }
    }
}
