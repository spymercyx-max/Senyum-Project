<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->loadMissing(['distributorProfile.territory', 'profile']);
        $territory = $user->distributorProfile?->territory;

        $totalOutlets = $user->outlets()->count();
        $trxTotal = $user->transactions()->count();

        $metrics = [
            'activeOutlets' => $user->outlets()->where('status', 'active')->count(),
            'totalOutlets' => $totalOutlets,
            'poPending' => $user->purchaseOrders()->whereNotIn('status', ['selesai', 'ditolak'])->count(),
            'poTotal' => $user->purchaseOrders()->count(),
            'trxMonth' => $user->transactions()->where('sold_at', '>=', now()->startOfMonth())->count(),
            'trxTotal' => $trxTotal,
            'visitsWeek' => $user->visits()->where('visited_at', '>=', now()->startOfWeek())->count(),
        ];

        $followUps = $user->visits()->with('outlet')
            ->whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', '<=', today())
            ->orderBy('follow_up_at')
            ->limit(5)->get();

        $pendingPos = $user->purchaseOrders()
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->latest()->limit(5)->get();

        $scheduled = $user->visits()->with('outlet')
            ->where('status', 'planned')
            ->whereDate('visited_at', '>=', today())
            ->orderBy('visited_at')->limit(5)->get();

        $recentTrx = $user->transactions()->with(['outlet', 'items'])
            ->latest('sold_at')->limit(5)->get();

        $checklist = [
            ['label' => 'Lengkapi profil & nomor WhatsApp', 'done' => filled($user->phone) || filled($user->profile?->whatsapp)],
            ['label' => 'Wilayah kerja terpasang', 'done' => (bool) $territory],
            ['label' => 'Tambah outlet pertama', 'done' => $totalOutlets > 0],
            ['label' => 'Catat penjualan pertama', 'done' => $trxTotal > 0],
            ['label' => 'Jadwalkan kunjungan outlet', 'done' => $user->visits()->exists()],
        ];

        return view('distributor.dashboard', [
            'user' => $user,
            'territory' => $territory?->displayName(),
            'territoryModel' => $territory,
            'metrics' => $metrics,
            'followUps' => $followUps,
            'pendingPos' => $pendingPos,
            'scheduled' => $scheduled,
            'recentTrx' => $recentTrx,
            'checklist' => $checklist,
            'showOnboarding' => $totalOutlets === 0 || $trxTotal === 0,
        ]);
    }
}
