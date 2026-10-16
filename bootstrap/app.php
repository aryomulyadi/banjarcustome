<?php

use App\Http\Middleware\EnsureRoleIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // prepend: SecurityHeaders harus berjalan paling luar agar exception
        // dari middleware global lain (mis. PreventRequestsDuringMaintenance →
        // 503, ValidatePostSize → 413) tetap melewati try/catch dan mendapat
        // header keamanan.
        $middleware->prepend([
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'admin' => EnsureRoleIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
