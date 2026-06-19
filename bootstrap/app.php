<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->hasHeader('X-Livewire')) {
                // Convert database errors to validation errors so Filament displays them as a notification/form error
                // instead of crashing to a 500 internal server error page.
                throw \Illuminate\Validation\ValidationException::withMessages([
                    // Using a generic key, Filament will either attach it to a field or show it in the generic validation alert.
                    'database_error' => 'A database error occurred: ' . $e->errorInfo[2],
                ]);
            }
        });
    })->create();
