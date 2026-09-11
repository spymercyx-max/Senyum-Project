<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitRequest;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $visits = $user->visits()->with('outlet')
            ->when($request->filled('outlet_id'), fn ($q) => $q->where('outlet_id', $request->query('outlet_id')))
            ->latest('visited_at')
            ->paginate(10)
            ->withQueryString();

        $outlets = $user->outlets()->where('status', 'active')->orderBy('name')->get();

        return view('distributor.visit', [
            'visits' => $visits,
            'outlets' => $outlets,
            'filterOutlet' => $request->query('outlet_id'),
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function store(VisitRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $outlet = Outlet::findOrFail($data['outlet_id']);
        abort_if((int) $outlet->distributor_id !== (int) $user->id, 403, 'Outlet ini bukan milik Anda.');

        $visit = Visit::create(array_merge($data, [
            'distributor_id' => $user->id,
            'status' => $data['status'] ?? 'done',
        ]));

        ActivityLog::create([
            'actor_id' => $user->id,
            'action' => 'visit.created',
            'entity' => Visit::class,
            'entity_id' => $visit->id,
            'metadata' => ['outlet' => $outlet->name],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('distributor.visit')
            ->with('success', "Kunjungan ke {$outlet->name} berhasil dicatat. Langkah berikutnya: tindak lanjuti outlet yang perlu follow-up.");
    }
}
