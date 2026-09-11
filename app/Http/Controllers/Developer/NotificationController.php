<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($request->query('read') === 'all') {
            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return redirect()->route('developer.notifikasi')
                ->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('developer.notifikasi', compact('notifications', 'unreadCount'));
    }
}
