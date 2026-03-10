<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Mark as read
        $notification->markAsRead();

        // Redirect to related page
        if (isset($notification->data['pengajuan_id'])) {
            return redirect()->route('pengajuan.show', $notification->data['pengajuan_id']);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read');
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        // Cegah user membaca notifikasi milik orang lain
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update([
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
