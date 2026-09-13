<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest();

        if ($request->string('filter')->toString() === 'unread') {
            $notifications->whereNull('read_at');
        }

        return view('admin.notifications.index', [
            'notifications' => $notifications->paginate(20)->withQueryString(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Wszystkie powiadomienia oznaczono jako przeczytane.');
    }
}
