<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['en', 'pt_BR', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->preferred_locale
            ?? $this->normalizeLocale($request->getPreferredLanguage(['en', 'pt-BR', 'es']));

        app()->setLocale($locale);
        Carbon::setLocale(str_replace('_', '-', $locale));

        return $next($request);
    }

    private function normalizeLocale(?string $locale): string
    {
        $normalized = str_replace('-', '_', (string) $locale);

        if (in_array($normalized, self::SUPPORTED, true)) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'pt')) {
            return 'pt_BR';
        }

        if (str_starts_with($normalized, 'es')) {
            return 'es';
        }

        return 'en';
    }
}
