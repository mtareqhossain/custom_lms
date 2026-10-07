<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Replaces Breeze's default AuthenticatedSessionController to add email-based
 * two-factor authentication. The flow:
 *
 *   1. Normal credential check (unchanged from Breeze).
 *   2. If the user has 2FA enabled, we do NOT call Auth::login() yet. Instead
 *      we generate a 6-digit code, store its hash + expiry on the user row,
 *      email it, and stash the user's id in the session under a
 *      "2fa_challenge_user_id" key. We redirect to the challenge screen.
 *   3. TwoFactorController verifies the code against that pending user id and
 *      only THEN calls Auth::login() for real.
 *
 * This matters because skipping step 2 (e.g. calling Auth::login() first and
 * "logging out" afterwards) would briefly create a real authenticated session
 * that other middleware or race conditions could observe. We never authenticate
 * until the second factor is verified.
 */
class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact your administrator.',
            ]);
        }

        if ($user->two_factor_enabled) {
            $code = (string) random_int(100000, 999999);

            $user->forceFill([
                'two_factor_code' => bcrypt($code),
                'two_factor_expires_at' => now()->addMinutes(10),
                'two_factor_attempts' => 0,
            ])->save();

            $user->notify(new TwoFactorCodeNotification($code));

            $request->session()->put('2fa_challenge_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);
        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
