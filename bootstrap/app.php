<?php

use Illuminate\Auth\AuthenticationException;
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
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (AuthenticationException $exception) {
        // Check the request type (JSON or web)
        if (request()->expectsJson()) {
          return response()->json(false, 401);
        }

        // Redirect for web requests
        return redirect()->guest(route('auth.login'));
      });
    })->create();
