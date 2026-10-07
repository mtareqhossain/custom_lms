<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function show(Request $request)
    {
        if (!$request->session()->has('2fa_challenge_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get('2fa_challenge_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user || !$user->two_factor_code || !$user->two_factor_expires_at) {
            $request->session()->forget(['2fa_challenge_user_id', '2fa_remember']);
            return redirect()->route('login')->withErrors(['code' => 'Your session expired. Please log in again.']);
        }

        if (now()->greaterThan($user->two_factor_expires_at)) {
            $request->session()->forget(['2fa_challenge_user_id', '2fa_remember']);
            return redirect()->route('login')->withErrors(['code' => 'That code has expired. Please log in again.']);
        }

        if ($user->two_factor_attempts >= self::MAX_ATTEMPTS) {
            $request->session()->forget(['2fa_challenge_user_id', '2fa_remember']);
            $user->forceFill(['two_factor_code' => null, 'two_factor_expires_at' => null])->save();
            return redirect()->route('login')->withErrors(['code' => 'Too many incorrect attempts. Please log in again.']);
        }

        if (!Hash::check($request->input('code'), $user->two_factor_code)) {
            $user->increment('two_factor_attempts');
            throw ValidationException::withMessages(['code' => 'That code is incorrect.']);
        }

        // Success — clear the pending code and log in for real.
        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_attempts' => 0,
        ])->save();

        $remember = $request->session()->get('2fa_remember', false);
        $request->session()->forget(['2fa_challenge_user_id', '2fa_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // return redirect()->intended(RouteServiceProvider::HOME);
        // return redirect()->route('dashboard');
        return redirect()->intended('/dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_challenge_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login');
        }

        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'two_factor_code' => bcrypt($code),
            'two_factor_expires_at' => now()->addMinutes(10),
            'two_factor_attempts' => 0,
        ])->save();

        $user->notify(new \App\Notifications\TwoFactorCodeNotification($code));

        return back()->with('status', 'A new code has been sent to your email.');
    }
}
