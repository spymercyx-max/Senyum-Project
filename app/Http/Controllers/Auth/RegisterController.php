<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterDistributorRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(RegisterDistributorRequest $request, RegistrationService $service): RedirectResponse
    {
        $user = $service->register($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('distributor.pending');
    }
}
