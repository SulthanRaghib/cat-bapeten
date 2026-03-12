<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** Supported locales for this application. */
    private const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'id'));

        if (in_array($locale, self::SUPPORTED, strict: true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
