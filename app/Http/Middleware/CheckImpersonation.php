<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonating_admin_id')) {
            // Share the impersonation state with all views
            view()->share('impersonating', true);
            view()->share('impersonated_name', auth()->user()->name ?? 'Unknown');
        }

        return $next($request);
    }
}
