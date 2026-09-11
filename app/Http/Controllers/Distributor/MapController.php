<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Services\MapService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(Request $request, MapService $maps): View
    {
        $user = $request->user();

        $outlets = $user->outlets()->with('territory')->orderBy('name')->get();

        $center = $maps->defaultCenter();
        $mapsKey = $maps->browserKey();

        $payload = $outlets->map(function ($o) use ($user) {
            $lastVisit = $o->visits()->where('distributor_id', $user->id)->latest('visited_at')->first();
            $lastTrx = $o->transactions()->where('distributor_id', $user->id)->latest('sold_at')->first();

            return [
                'id' => $o->id,
                'nama' => $o->name,
                'alamat' => $o->address,
                'kota' => $o->city,
                'kecamatan' => $o->district,
                'status' => $o->status,
                'lat' => $o->latitude !== null ? (float) $o->latitude : null,
                'lng' => $o->longitude !== null ? (float) $o->longitude : null,
                'last_visit' => $lastVisit?->visited_at?->format('d M Y'),
                'last_transaksi' => $lastTrx?->sold_at?->format('d M Y'),
                'url' => [
                    'detail' => route('distributor.outlet.show', $o),
                    'route' => $o->hasCoordinates()
                        ? 'https://www.google.com/maps/search/?api=1&query='.$o->latitude.','.$o->longitude
                        : ($o->google_maps_url ?: null),
                    'edit' => route('distributor.outlet.edit', $o),
                ],
            ];
        })->values();

        return view('distributor.peta', [
            'outlets' => $outlets,
            'outletsJson' => $payload,
            'mapsKey' => $mapsKey,
            'center' => $center,
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }
}
