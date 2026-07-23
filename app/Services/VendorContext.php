<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VendorContext
{
    /**
     * Resolve the current vendor from the authenticated user or
     * the admin impersonation session.
     */
    public static function current(): Vendor
    {
        if (Session::has('acting_as_vendor_id')) {
            return Vendor::findOrFail(Session::get('acting_as_vendor_id'));
        }

        $user = Auth::user();

        if (! $user) {
            abort(403, 'No authenticated user found.');
        }

        $vendor = $user->vendor;

        if (! $vendor) {
            abort(403, 'No vendor record associated with this account.');
        }

        return $vendor;
    }

    /**
     * Return the current vendor's ID without loading the full model.
     */
    public static function currentId(): int
    {
        if (Session::has('acting_as_vendor_id')) {
            return (int) Session::get('acting_as_vendor_id');
        }

        $user = Auth::user();

        if (! $user || !$user->vendor) {
            abort(403, 'No vendor context available.');
        }

        return $user->vendor->id;
    }

    /**
     * Set the admin to act as a specific vendor (impersonation).
     */
    public static function actingAs(int $vendorId): void
    {
        Session::put('acting_as_vendor_id', $vendorId);
    }

    /**
     * Stop admin impersonation.
     */
    public static function stopActing(): void
    {
        Session::forget('acting_as_vendor_id');
    }

    /**
     * Check if the current session is an admin impersonating a vendor.
     */
    public static function isImpersonating(): bool
    {
        return Session::has('acting_as_vendor_id');
    }
}
