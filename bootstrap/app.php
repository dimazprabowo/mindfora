<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        // SetLocale reads locale from cookie (not session), so it can run in
        // the global stack before StartSession — same pattern as dark mode's
        // localStorage, but server-readable via cookie on every request.
        $middleware->append(\App\Http\Middleware\SetLocale::class);

        // Trust the local reverse proxy (cloudflared / ngrok / Caddy) so that
        // URL::current() and asset() generate https:// URLs when the tunnel
        // terminates TLS upstream. cloudflared connects from 127.0.0.1 and
        // sets X-Forwarded-Proto/Host — trusting '*' is safe here because the
        // only proxy is the local tunnel process.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'sso.secret' => \App\Http\Middleware\VerifySsoSecret::class,
            'registration.open' => \App\Http\Middleware\CheckRegistrationOpen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
