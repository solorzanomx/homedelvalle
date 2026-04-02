<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get recent notifications for the current user (JSON).
     */
    public function index(Request $request)
    {
        $notifications = Auth::user()->notifications()
            ->with('fromUser')
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'read' => (bool) $n->read_at,
                'from_name' => $n->fromUser?->full_name,
                'from_avatar' => $n->fromUser?->avatar_path
                    ? \Storage::url($n->fromUser->avatar_path)
                    : null,
                'from_initial' => $n->fromUser
                    ? strtoupper(substr($n->fromUser->name, 0, 1))
                    : '?',
                'time_ago' => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * Search users for @mention autocomplete (JSON).
     */
    public function searchUsers(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('last_name', 'LIKE', "%{$q}%")
                      ->orWhere('email', 'LIKE', "%{$q}%");
            })
            ->limit(8)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name,
                'email' => $u->email,
                'avatar' => $u->avatar_path
                    ? \Storage::url($u->avatar_path)
                    : null,
                'initial' => strtoupper(substr($u->name, 0, 1)),
                'title' => $u->title,
            ]);

        return response()->json($users);
    }
}
