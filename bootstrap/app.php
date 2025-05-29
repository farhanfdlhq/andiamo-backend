<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter; // Pastikan ini di-import jika Anda customize
use Illuminate\Cache\RateLimiting\Limit;    // Pastikan ini di-import jika Anda customize
use Illuminate\Http\Request;                 // Pastikan ini di-import jika Anda customize

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        // Biasanya rate limiter 'api' didefinisikan di sini secara default
        // atau melalui konfigurasi middleware API.
        // Jika tidak ada, kita bisa tambahkan konfigurasi RateLimiter::for
        // di dalam metode 'boot' dari AppServiceProvider atau di sini.
        // Namun, Laravel 11+ cenderung lebih otomatis.
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware grup 'api' biasanya sudah memiliki 'throttle:api'
        // Jika 'throttle:api' ada, maka rate limiter 'api' HARUS terdefinisi.
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api', // <--- INI MEMBUTUHKAN RATE LIMITER 'api'
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ... (middleware lainnya) ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();
