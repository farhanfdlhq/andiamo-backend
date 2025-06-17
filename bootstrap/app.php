<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter; // Pastikan ini di-import
use Illuminate\Cache\RateLimiting\Limit;    // Pastikan ini di-import
use Illuminate\Http\Request;                // Pastikan ini di-import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Konfigurasi untuk EncryptCookies
        // Pastikan XSRF-TOKEN dikecualikan dari enkripsi
        $middleware->encryptCookies(except: [
            'XSRF-TOKEN',
            'laravel_session',
            // tambahkan nama cookie lain di sini jika ada yang tidak ingin dienkripsi
        ]);
        
        $middleware->trustProxies(at: '*');

        // Middleware group untuk API
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api', // Ini akan menggunakan rate limiter bernama 'api'
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Jika ada middleware global lain yang ingin kamu daftarkan,
        // kamu bisa menambahkannya di sini, contoh:
        // $middleware->append(MyCustomGlobalMiddleware::class);
        // $middleware->prepend(AnotherGlobalMiddleware::class);

        // Untuk alias middleware, kamu bisa mendaftarkannya seperti ini:
        // $middleware->alias([
        //     'isAdmin' => \App\Http\Middleware\AdminMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan exception bisa ditambahkan di sini
        // Contoh:
        // $exceptions->dontReport([
        //     \App\Exceptions\CustomException::class,
        // ]);
        // $exceptions->report(function (Throwable $e) {
        //     // Logika pelaporan kustom
        // });
    })
    ->booted(function () { // Konfigurasi RateLimiter bisa diletakkan di sini atau di AppServiceProvider::boot()
        RateLimiter::for('api', function (Request $request) {
            // Tingkatkan batas menjadi 200 request per menit (atau sesuai kebutuhan Anda)
            // Anda bisa menggunakan ->perHour(), ->perDay() juga.
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });
    })->create();