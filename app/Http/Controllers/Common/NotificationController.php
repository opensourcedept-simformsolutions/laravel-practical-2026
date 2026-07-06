<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);

        $visitorLogIds = $notifications->filter(function ($n) {
            return $n->type === 'App\Notifications\VisitorStatusNotification';
        })->map(function ($n) {
            return $n->data['visitor_log_id'] ?? null;
        })->filter()->unique();

        $visitorLogs = \App\Models\VisitorLog::whereIn('id', $visitorLogIds)->get()->keyBy('id');

        return view('notifications.index', compact('notifications', 'visitorLogs'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with([
            'message' => 'All notifications marked as read.',
            'status' => 'success',
        ]);
    }
}
