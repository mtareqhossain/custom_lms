<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {


        // Honeypot: a real visitor never fills this in
    if ($request->filled('website')) {
        abort(422);
    }

    // Time trap: a bot that submits in under 3 seconds didn't actually read the form
    if ($request->filled('form_rendered_at') && (time() - (int) $request->form_rendered_at) < 3) {
        abort(422);
    }

    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'unique:' . User::class,
            function ($attribute, $value, $fail) {
                $domain = strtolower(substr(strrchr($value, '@'), 1));
                $blocked = ['mailinator.com', 'tempmail.com', 'guerrillamail.com', '10minutemail.com', 'yopmail.com', 'trashmail.com', 'throwawaymail.com'];
                if (in_array($domain, $blocked, true)) {
                    $fail('Please use a permanent email address.');
                }
            },
        ],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);
    $verify = \Illuminate\Support\Facades\Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
    'secret' => config('services.turnstile.secret_key'),
    'response' => $request->input('cf-turnstile-response'),
    'remoteip' => $request->ip(),
]);

if (! $verify->json('success')) {
    throw ValidationException::withMessages(['email' => 'Verification failed — please try again.']);
}

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        \App\Services\TemplateMailer::send(
            'welcome_student',
            $user->email,
            $user->name,
        [
        'student_name' => $user->name,
        'app_name' => config('app.name'),
        'login_url' => route('login'),
        ]
);
        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
