<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\MetalRate;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCategories = Category::active()
            ->topLevel()
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $featuredProducts = Product::active()
            ->featured()
            ->with(['primaryImage', 'vendor', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $newArrivals = Product::active()
            ->newArrivals()
            ->with(['primaryImage', 'vendor'])
            ->latest()
            ->limit(8)
            ->get();

        $featuredVendors = Vendor::approved()
            ->withCount('products')
            ->orderByDesc('total_sales')
            ->limit(6)
            ->get();

        $goldRate = MetalRate::getLatestRate('gold', '22K');

        return view('home', compact(
            'featuredCategories',
            'featuredProducts',
            'newArrivals',
            'featuredVendors',
            'goldRate'
        ));
    }
}
