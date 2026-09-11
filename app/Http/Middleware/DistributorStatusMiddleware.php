<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class DistributorStatusMiddleware
{
    public function handle(Request $request, Closure $next, string $allowed = 'approved'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isDeveloper()) {
            return $next($request);
        }

        $status = $user->distributorProfile?->status ?? 'pending';
        $allowedList = array_map('trim', explode(',', $allowed));

        if (in_array($status, $allowedList, true)) {
            return $next($request);
        }

        $routeMap = [
            'pending' => 'distributor.pending',
            'rejected' => 'distributor.rejected',
            'suspended' => 'distributor.suspended',
        ];

        $pathMap = [
            'pending' => '/distributor/pending',
            'rejected' => '/distributor/rejected',
            'suspended' => '/distributor/suspended',
        ];

        $routeName = $routeMap[$status] ?? null;

        if ($routeName && Route::has($routeName)) {
            return redirect()->route($routeName);
        }

        if (isset($pathMap[$status])) {
            return redirect($pathMap[$status]);
        }

        abort(403, 'Status distributor tidak diizinkan.');
    }
}
