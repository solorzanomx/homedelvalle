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
        // Middleware global para redirigir admin.homedelvalle.mx a /admin
        $middleware->append(\App\Http\Middleware\SubdomainRedirect::class);

        $middleware->alias([
            'admin'        => \App\Http\Middleware\CheckAdminRole::class,
            'broker'       => \App\Http\Middleware\CheckBrokerRole::class,
            'editor'       => \App\Http\Middleware\CheckEditorRole::class,
            'viewer'       => \App\Http\Middleware\CheckViewerRole::class,
            'client'       => \App\Http\Middleware\CheckClientRole::class,
            'permission'   => \App\Http\Middleware\CheckPermission::class,
            'portal.legal' => \App\Http\Middleware\EnsurePortalLegalAcceptance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
