<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Notification::with('user');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $notifications = $query->latest()->get();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function show(Notification $notification): View
    {
        $notification->load('user');

        return view('admin.notifications.show', compact('notification'));
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return to_route('admin.notifications.index')->with('success', __('Notification deleted successfully.'));
    }
}
