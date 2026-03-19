<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Permite que Laravel detecte correctamente el esquema real (https) cuando hay proxy/Cloudflare.
        // Esto evita problemas como "Mixed Content" por URLs internas que salen como http.
        // Solo necesitamos el esquema real (proto) para evitar que Laravel genere links http cuando el navegador usa https.
        Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
