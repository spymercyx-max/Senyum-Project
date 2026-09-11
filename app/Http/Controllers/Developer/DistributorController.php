<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Territory;
use App\Models\User;
use App\Services\DistributorApprovalService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistributorController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'suspended'];

    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, self::STATUSES, true)) {
            $status = 'pending';
        }

        $counts = [];
        foreach (self::STATUSES as $s) {
            $counts[$s] = User::where('role', 'distributor')
                ->whereHas('distributorProfile', fn ($q) => $q->where('status', $s))
                ->count();
        }

        $distributors = User::where('role', 'distributor')
            ->whereHas('distributorProfile', fn ($q) => $q->where('status', $status))
            ->with(['profile', 'distributorProfile.territory'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('developer.distributor', compact('status', 'counts', 'distributors'));
    }

    public function show(User $user): View
    {
        abort_if($user->role !== 'distributor', 404, 'Distributor tidak ditemukan.');

        $user->load(['profile', 'distributorProfile.territory', 'distributorProfile.approver']);

        $outlets = $user->outlets()->with('territory')->latest()->take(10)->get();
        $outletCount = $user->outlets()->count();
        $orders = $user->purchaseOrders()->with('items.product')->latest()->take(10)->get();
        $transactions = $user->transactions()->with('outlet')->latest()->take(10)->get();
        $activity = ActivityLog::with('actor')
            ->where('actor_id', $user->id)
            ->orWhere(fn ($q) => $q->where('entity', User::class)->where('entity_id', $user->id))
            ->latest()
            ->take(15)
            ->get();
        $territories = Territory::orderBy('city')->orderBy('district')->get();

        return view('developer.distributor-detail', compact(
            'user',
            'outlets',
            'outletCount',
            'orders',
            'transactions',
            'activity',
            'territories'
        ));
    }

    public function approve(User $user, DistributorApprovalService $service): RedirectResponse
    {
        abort_if($user->role !== 'distributor', 404, 'Distributor tidak ditemukan.');

        try {
            $service->approve($this->developer(), $user);
        } catch (ModelNotFoundException) {
            return back()->with('error', 'Profil distributor tidak ditemukan sehingga tidak bisa disetujui.');
        } catch (\Throwable) {
            return back()->with('error', 'Gagal menyetujui distributor. Silakan coba lagi.');
        }

        return back()->with('success', "Distributor {$user->name} berhasil disetujui.");
    }

    public function reject(Request $request, User $user, DistributorApprovalService $service): RedirectResponse
    {
        abort_if($user->role !== 'distributor', 404, 'Distributor tidak ditemukan.');

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $service->reject($this->developer(), $user, $data['reason'] ?? null);
        } catch (ModelNotFoundException) {
            return back()->with('error', 'Profil distributor tidak ditemukan sehingga tidak bisa ditolak.');
        } catch (\Throwable) {
            return back()->with('error', 'Gagal menolak distributor. Silakan coba lagi.');
        }

        return back()->with('success', "Pengajuan distributor {$user->name} berhasil ditolak.");
    }

    public function suspend(Request $request, User $user, DistributorApprovalService $service): RedirectResponse
    {
        abort_if($user->role !== 'distributor', 404, 'Distributor tidak ditemukan.');

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $service->suspend($this->developer(), $user, $data['reason'] ?? null);
        } catch (ModelNotFoundException) {
            return back()->with('error', 'Profil distributor tidak ditemukan sehingga tidak bisa ditangguhkan.');
        } catch (\Throwable) {
            return back()->with('error', 'Gagal menangguhkan distributor. Silakan coba lagi.');
        }

        return back()->with('success', "Distributor {$user->name} berhasil ditangguhkan.");
    }

    private function developer(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
