<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }



    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        RateLimiter::for('chatbot', function (\Illuminate\Http\Request $request) {
        return Limit::perMinutes(10, 20)->by($request->user()?->id ?: $request->ip());
    });
    Gate::before(function ($user, $ability) {
    return $user->hasRole('admin') ? true : null;
    });
    }
}
