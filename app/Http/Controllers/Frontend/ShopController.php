<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['primaryImage', 'vendor', 'category']);

        // Category filter
        $selectedCategory = null;
        if ($request->filled('category')) {
            $selectedCategory = Category::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $childIds = $selectedCategory->children()->pluck('id')->prepend($selectedCategory->id);
                $query->whereIn('category_id', $childIds);
            }
        }

        // Metal type filter
        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->metal_type);
        }

        // Purity filter
        if ($request->filled('purity')) {
            $query->where('purity', $request->purity);
        }

        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Certification filter
        if ($request->filled('certification')) {
            $query->where('certification_type', $request->certification);
        }

        // Stock only
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Featured / New Arrivals flags
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if ($request->boolean('new_arrivals')) {
            $query->where('is_new_arrival', true);
        }

        // Search
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('short_description', 'like', "%{$term}%");
            });
        }

        // Sort
        switch ($request->get('sort', 'newest')) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'popular':
                $query->withCount('orderItems')->orderByDesc('order_items_count');
                break;
            default:
                $query->latest();
        }

        $products      = $query->paginate(24)->withQueryString();
        $categories    = Category::active()->topLevel()->with('children')->get();
        $subCategories = Category::active()->whereNotNull('parent_id')->orderBy('sort_order')->orderBy('name')->get();
        $vendors       = Vendor::approved()->orderBy('store_name')->get(['id', 'store_name']);

        // Determine title text
        if ($request->filled('search')) {
            $title = 'Search: "' . htmlspecialchars($request->search) . '"';
        } elseif ($selectedCategory) {
            $title = htmlspecialchars($selectedCategory->name);
        } elseif ($request->filled('metal_type')) {
            $title = ucfirst($request->metal_type) . ' Jewellery';
        } else {
            $title = 'All Jewellery';
        }

        // Count text
        $count = $products->total() . ' ' . \Illuminate\Support\Str::plural('product', $products->total()) . ' found';

        // Showing results range text
        if ($products->total() > 0) {
            $showing = 'Showing ' . $products->firstItem() . '–' . $products->lastItem() . ' of ' . $products->total();
        } else {
            $showing = 'Showing 0–0 of 0';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'title' => $title,
                'count' => $count,
                'showing' => $showing,
                'active_filters' => view('shop.partials.active-filters', compact('selectedCategory', 'vendors'))->render(),
                'grid' => view('shop.partials.products-grid', compact('products'))->render(),
                'pagination' => $products->links()->render()
            ]);
        }

        return view('shop.index', compact('products', 'categories', 'subCategories', 'vendors', 'selectedCategory'));
    }

    public function byCategory(string $slug)
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();
        return redirect()->route('shop.index', ['category' => $slug]);
    }

    public function byVendor(string $slug)
    {
        $vendor = Vendor::where('store_slug', $slug)->approved()->firstOrFail();
        return redirect()->route('shop.index', ['vendor_id' => $vendor->id]);
    }
}
