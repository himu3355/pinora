<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of all active, approved vendors.
     */
    public function index()
    {
        $vendors = Vendor::where('status', 'approved')
            ->orderBy('store_name', 'asc')
            ->paginate(12);

        return view('vendors.index', compact('vendors'));
    }

    /**
     * Display the storefront of a specific vendor.
     */
    public function show(string $slug)
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $isOwner = auth()->check() && auth()->user()->vendor && auth()->user()->vendor->id === $vendor->id;
        if (! $vendor->hasActiveSubscription() && ! $isOwner) {
            abort(404, 'This shop is temporarily unavailable.');
        }

        // Paginate active products belonging to this vendor
        $products = $vendor->products()
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('vendors.show', compact('vendor', 'products'));
    }
}
