<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $counts = [
            'total' => Outlet::count(),
            'active' => Outlet::where('status', 'active')->count(),
            'inactive' => Outlet::where('status', 'inactive')->count(),
            'pending' => Outlet::where('status', 'pending')->count(),
        ];

        $outlets = Outlet::with(['territory', 'distributor'])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('district', 'like', "%{$q}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('developer.outlet', compact('outlets', 'counts', 'q'));
    }
}
