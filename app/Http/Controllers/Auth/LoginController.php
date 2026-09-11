<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $login = trim($data['login']);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $remember = $request->boolean('remember');

        if (! Auth::attempt([$field => $login, 'password' => $data['password']], $remember)) {
            return back()
                ->withErrors(['login' => 'Kredensial tidak cocok dengan data kami.'])
                ->onlyInput('login', 'remember');
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || $user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['login' => 'Akun Anda tidak aktif. Hubungi admin untuk bantuan.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        ActivityLog::create([
            'actor_id' => $user->id,
            'action' => 'login',
            'entity' => User::class,
            'entity_id' => $user->id,
            'metadata' => ['username' => $user->username],
            'ip' => $request->ip(),
        ]);

        if ($user->isDeveloper()) {
            return redirect()->intended(route('developer.dashboard'));
        }

        $status = $user->distributorStatus() ?? 'pending';

        return match ($status) {
            'approved' => redirect()->intended(route('distributor.dashboard')),
            'rejected' => redirect()->intended(route('distributor.rejected')),
            'suspended' => redirect()->intended(route('distributor.suspended')),
            default => redirect()->intended(route('distributor.pending')),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::create([
                'actor_id' => $user->id,
                'action' => 'logout',
                'entity' => User::class,
                'entity_id' => $user->id,
                'metadata' => ['username' => $user->username],
                'ip' => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
