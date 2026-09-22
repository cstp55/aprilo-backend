<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    /**
     * Single-Use SSO login into Admin console after onboarding.
     * GET /admin/sso?token=...
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        if (! $token) {
            return redirect()->route('admin.login')->withErrors(['email' => 'SSO token is required.']);
        }

        $onboarding = OnboardingRequest::where('sso_token', $token)
            ->where('sso_token_expires_at', '>', now())
            ->first();

        if (! $onboarding || ! $onboarding->user_id) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Invalid or expired single-use SSO token. Please log in with your credentials.']);
        }

        $user = User::find($onboarding->user_id);
        if (! $user) {
            return redirect()->route('admin.login')->withErrors(['email' => 'User not found.']);
        }

        // Invalidate single-use token
        $onboarding->update([
            'sso_token' => null,
            'sso_token_expires_at' => null,
        ]);

        // Log into web session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('status', 'Welcome to Aprilo Infotech! Your account is active with 1-Month Free Trial.');
    }
}