<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set application locale from cookie, user preference, or default.
     *
     * Uses cookie (not session) so this middleware can run in the global stack
     * before StartSession — consistent with how dark mode uses localStorage.
     * Priority: cookie > user->locale > 'id' (Indonesian-first app).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale');

        if (! $locale && auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
        }

        if (! $locale) {
            // App is Indonesian-first; default to 'id' when no preference is set.
            $locale = 'id';
        }

        if (in_array($locale, ['id', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
