<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->load('profile');

        $activityCount = ActivityLog::where('actor_id', $user->id)->count();
        $recentActivity = ActivityLog::where('actor_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('developer.profil', compact('user', 'activityCount', 'recentActivity'));
    }
}
