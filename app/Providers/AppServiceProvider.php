<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit; // Tambahkan ini
use Illuminate\Http\Request;             // Tambahkan ini
use Illuminate\Support\Facades\RateLimiter; // Tambahkan ini
use Illuminate\Support\ServiceProvider;

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
        // Definisikan rate limiter 'api' di sini
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Anda juga bisa mendefinisikan rate limiter lain jika perlu
        // RateLimiter::for('uploads', function (Request $request) {
        //     return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        // });
    }
}
