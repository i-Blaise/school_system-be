<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Retrieve a list of notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        // Get paginated notifications (both read and unread)
        $notifications = $request->user()->notifications()->paginate(15);
        
        // Also provide a quick badge count of unread specifically
        $unreadCount = $request->user()->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read, or all if no ID is passed.
     */
    public function markAsRead(Request $request, $id = null)
    {
        if ($id) {
            $notification = $request->user()->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
            } else {
                return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
            }
        } else {
            // Mark all as read
            $request->user()->unreadNotifications->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read.'
        ]);
    }
}
