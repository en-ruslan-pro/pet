<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBrowserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->getPreferredLanguage();

        app()->setLocale(is_string($language) && str_starts_with(strtolower($language), 'uk') ? 'uk' : 'en');

        return $next($request);
    }
}
