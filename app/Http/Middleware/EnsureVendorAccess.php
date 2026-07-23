<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('filament.vendor.auth.login');
        }

        $hasVendorRole   = $user->hasRole(['vendor', 'vendor_staff']);
        $isActingAsVendor = session()->has('acting_as_vendor_id');

        if (! $hasVendorRole && ! $isActingAsVendor) {
            session()->flash('error', 'You do not have access to the vendor panel.');
            return redirect('/admin');
        }

        return $next($request);
    }
}
