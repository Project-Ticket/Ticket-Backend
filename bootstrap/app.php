<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'rest/api'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('rest/api/auth/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
