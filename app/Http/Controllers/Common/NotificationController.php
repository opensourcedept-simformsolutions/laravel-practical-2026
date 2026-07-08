<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = auth()->user()->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Order by latest first
        $query->latest();

        $notifications = $query->paginate(15)->appends(['filter' => $filter]);

        $visitorLogIds = $notifications->filter(function ($n) {
            return $n->type === 'App\Notifications\VisitorStatusNotification';
        })->map(function ($n) {
            return $n->data['visitor_log_id'] ?? null;
        })->filter()->unique();

        $visitorLogs = VisitorLog::whereIn('id', $visitorLogIds)->get()->keyBy('id');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('notifications._list', compact('notifications', 'visitorLogs', 'filter'))->render(),
                'unread_count' => auth()->user()->unreadNotifications->count(),
                'read_count' => auth()->user()->notifications()->whereNotNull('read_at')->count(),
                'all_count' => auth()->user()->notifications()->count(),
            ]);
        }

        return view('notifications.index', compact('notifications', 'visitorLogs', 'filter'));
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

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully.',
            ]);
        }

        return redirect()->back()->with([
            'message' => 'Notification deleted successfully.',
            'status' => 'success',
        ]);
    }
}
