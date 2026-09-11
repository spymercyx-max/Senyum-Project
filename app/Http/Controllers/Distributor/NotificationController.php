<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NotificationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($request->filled('read')) {
            $notification = Notification::where('user_id', $user->id)->findOrFail($request->query('read'));
            $notification->markAsRead();

            return redirect()->route('distributor.notifikasi');
        }

        $notifications = $user->notifications()->latest()->paginate(15);

        // Semua yang tampil dianggap sudah dibaca.
        $user->notifications()->unread()->update(['read_at' => now()]);

        return view('distributor.notifikasi', [
            'notifications' => $notifications,
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }
}
