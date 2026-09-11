<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing(['profile', 'distributorProfile.territory']);
        $territory = $user->distributorProfile?->territory;

        return view('distributor.profil', [
            'user' => $user,
            'territoryModel' => $territory,
            'territory' => $territory?->displayName(),
        ]);
    }

    public function help(Request $request, WhatsAppService $wa): View
    {
        return view('distributor.bantuan', [
            'waUrl' => $wa->waUrl(
                $wa->developerNumber(),
                'Halo SENYUM! Saya distributor dan butuh bantuan operasional. Terima kasih.'
            ),
            'territory' => $request->user()->distributorProfile?->territory?->displayName(),
        ]);
    }
}
