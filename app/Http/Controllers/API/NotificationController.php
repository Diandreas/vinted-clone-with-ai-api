<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->with(['notifiable'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead($notification)
    {
        $notification = Auth::user()->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function destroy($notification)
    {
        $notification = Auth::user()->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'marketing_notifications' => 'boolean',
            'product_updates' => 'boolean',
            'order_updates' => 'boolean',
            'social_updates' => 'boolean',
            'live_notifications' => 'boolean'
        ]);

        $user = Auth::user();
        $settings = $user->notification_settings ?? [];

        foreach ($request->only([
            'email_notifications', 'push_notifications', 'marketing_notifications',
            'product_updates', 'order_updates', 'social_updates', 'live_notifications'
        ]) as $key => $value) {
            $settings[$key] = $value;
        }

        $user->update(['notification_settings' => $settings]);

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Notification settings updated'
        ]);
    }
}