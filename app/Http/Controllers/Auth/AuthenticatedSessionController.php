<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (Auth::user()->status !== 'active') {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Log the user out
            Auth::guard('web')->logout();

            return back()->withErrors([
                'email' => 'This account has been deactivated.',
            ]);
        }

        $request->session()->regenerate();

        if (Auth::user()->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        if (Auth::user()->role === 'supervisor') {
            return redirect()->intended(route('supervisor.dashboard'));
        }

        if (Auth::user()->role === 'student') {
            return redirect()->intended(route('student.dashboard'));
        }
        // Default redirect for all other roles
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
