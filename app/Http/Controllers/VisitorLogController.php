<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\Request;

class VisitorLogController extends Controller
{
    public function pending()
    {
        $visitorLogs = VisitorLog::with(['visitor','flat'])->latest()->paginate(5);
        return view('gatekeeper.visitor-logs.pending', compact('visitorLogs'));
    }

    public function markEntry(VisitorLog $visitorLog)
    {
        if($visitorLog->status !== 'pending')
        {
            return back()->with('error','Only Pending Passes Can Be Entered!');
        }

        $visitorLog->update([
            'entry_time' => now(),
            'gatekeeper_id' => auth()->id(),
            'status' => 'entered',
        ]);

        return back()->with('success', 'Visitor Entery Marked Successfully!');
    }

    public function markExit(VisitorLog $visitorLog)
    {
        if($visitorLog->status !== 'entered')
        {
            return back()->with('error','Only Enetered Visitors Can Exit');
        }

        $visitorLog->update([
            'exit_time' => now(),
            'status' =>'exited',
        ]);

        return back()->with('success', 'Visitor exit marked Successfully!');
    }
}
