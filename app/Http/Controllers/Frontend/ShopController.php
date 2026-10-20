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
        $parentCategory = null;
        $sliderCategories = null;
        $isSubCategoryMode = false;

        if ($request->filled('category')) {
            $selectedCategory = Category::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $childIds = $selectedCategory->children()->pluck('id')->prepend($selectedCategory->id);
                $query->whereIn('category_id', $childIds);

                // If selected category is a main category and has subcategories:
                if ($selectedCategory->is_top_level) {
                    $children = $selectedCategory->children()->active()->orderBy('sort_order')->orderBy('name')->get();
                    if ($children->isNotEmpty()) {
                        $parentCategory = $selectedCategory;
                        $sliderCategories = $children;
                        $isSubCategoryMode = true;
                    }
                } else {
                    // It's a subcategory: find its parent main category
                    $parent = $selectedCategory->parent;
                    if ($parent) {
                        $children = $parent->children()->active()->orderBy('sort_order')->orderBy('name')->get();
                        if ($children->isNotEmpty()) {
                            $parentCategory = $parent;
                            $sliderCategories = $children;
                            $isSubCategoryMode = true;
                        }
                    }
                }
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
            $term = trim($request->search);
            $words = array_values(array_filter(explode(' ', $term), fn($w) => mb_strlen($w) >= 2));

            $query->where(function ($q) use ($term, $words) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('short_description', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('metal_type', 'like', "%{$term}%")
                  ->orWhere('purity', 'like', "%{$term}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"));

                if (count($words) > 1) {
                    $q->orWhere(function ($multiQ) use ($words) {
                        foreach ($words as $word) {
                            $multiQ->where(function ($sub) use ($word) {
                                $sub->where('name', 'like', "%{$word}%")
                                    ->orWhere('short_description', 'like', "%{$word}%")
                                    ->orWhere('sku', 'like', "%{$word}%")
                                    ->orWhere('metal_type', 'like', "%{$word}%")
                                    ->orWhere('purity', 'like', "%{$word}%")
                                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$word}%"));
                            });
                        }
                    });
                }
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
        $categories    = Category::active()->topLevel()->with('children')->orderBy('sort_order')->orderBy('name')->get();
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

        return view('shop.index', compact(
            'products',
            'categories',
            'subCategories',
            'vendors',
            'selectedCategory',
            'parentCategory',
            'sliderCategories',
            'isSubCategoryMode'
        ));
    }

    public function suggestions(Request $request)
    {
        $term = trim($request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'query' => $term,
                'products' => [],
                'categories' => [],
                'total_matches' => 0,
                'view_all_url' => route('shop.index'),
                'popular_searches' => [
                    '22K Gold Nose Pin',
                    'Floral Studs',
                    'Diamond Solitaire',
                    'Daily Wear',
                    'Silver Kada',
                    'Gold Ring'
                ]
            ]);
        }

        // Matching categories (up to 4)
        $categories = Category::active()
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->withCount(['products' => fn($p) => $p->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(4)
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'url' => route('shop.index', ['category' => $cat->slug]),
                'product_count' => $cat->products_count,
            ]);

        // Build product query with multi-field search
        $productQuery = Product::active()
            ->with(['primaryImage', 'category', 'vendor']);

        $words = array_values(array_filter(explode(' ', $term), fn($w) => mb_strlen($w) >= 2));

        $productQuery->where(function ($q) use ($term, $words) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('metal_type', 'like', "%{$term}%")
              ->orWhere('purity', 'like', "%{$term}%")
              ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"));

            if (count($words) > 1) {
                $q->orWhere(function ($multiQ) use ($words) {
                    foreach ($words as $word) {
                        $multiQ->where(function ($sub) use ($word) {
                            $sub->where('name', 'like', "%{$word}%")
                                ->orWhere('short_description', 'like', "%{$word}%")
                                ->orWhere('sku', 'like', "%{$word}%")
                                ->orWhere('metal_type', 'like', "%{$word}%")
                                ->orWhere('purity', 'like', "%{$word}%")
                                ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$word}%"));
                        });
                    }
                });
            }
        });

        $totalMatches = (clone $productQuery)->count();
        $products = $productQuery->latest()->take(5)->get()->map(function ($p) {
            $price = $p->calculated_price;
            $metalBadge = trim(($p->purity ?? '') . ' ' . ucfirst($p->metal_type ?? ''));
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'url' => route('product.show', $p->slug),
                'image_url' => $p->primary_image_url,
                'category_name' => $p->category?->name ?? 'Jewellery',
                'metal_badge' => $metalBadge ?: null,
                'price_formatted' => $price ? '₹' . number_format($price, 0) : ($p->is_price_on_request ? 'Price on Request' : ''),
            ];
        });

        return response()->json([
            'query' => $term,
            'products' => $products,
            'categories' => $categories,
            'total_matches' => $totalMatches,
            'view_all_url' => route('shop.index', ['search' => $term]),
        ]);
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
