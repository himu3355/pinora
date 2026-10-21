<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        // Metal type filter (supports multiple or single)
        if ($request->has('metal_type')) {
            $metals = is_array($request->metal_type) ? $request->metal_type : explode(',', (string) $request->metal_type);
            $metals = array_filter(array_map('trim', $metals));
            if (!empty($metals)) {
                $query->whereIn('metal_type', $metals);
            }
        }

        // Purity filter (supports multiple or single)
        if ($request->has('purity')) {
            $purities = is_array($request->purity) ? $request->purity : explode(',', (string) $request->purity);
            $purities = array_filter(array_map('trim', $purities));
            if (!empty($purities)) {
                $query->whereIn('purity', $purities);
            }
        }

        // Price Filter (Min / Max or Preset)
        $minPrice = $request->filled('min_price') ? (float) $request->min_price : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->max_price : null;

        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case 'under_5000':
                    $minPrice = 0;
                    $maxPrice = 5000;
                    break;
                case '5000_10000':
                    $minPrice = 5000;
                    $maxPrice = 10000;
                    break;
                case 'under_10000':
                    $minPrice = 0;
                    $maxPrice = 10000;
                    break;
                case '10000_25000':
                    $minPrice = 10000;
                    $maxPrice = 25000;
                    break;
                case '25000_50000':
                    $minPrice = 25000;
                    $maxPrice = 50000;
                    break;
                case '50000_100000':
                    $minPrice = 50000;
                    $maxPrice = 100000;
                    break;
                case 'above_50000':
                    $minPrice = 50000;
                    $maxPrice = null;
                    break;
                case 'above_100000':
                    $minPrice = 100000;
                    $maxPrice = null;
                    break;
            }
        }

        if ($minPrice !== null || $maxPrice !== null) {
            $candidateIds = (clone $query)->pluck('id');
            $matchingIds = Product::whereIn('id', $candidateIds)->get()->filter(function ($prod) use ($minPrice, $maxPrice) {
                $price = $prod->calculated_price ?? (float) $prod->base_price;
                if ($price === null) return false;
                if ($minPrice !== null && $price < $minPrice) return false;
                if ($maxPrice !== null && $price > $maxPrice) return false;
                return true;
            })->pluck('id');

            $query->whereIn('id', $matchingIds);
        }

        // Weight Filter (Min / Max or Preset in grams)
        $minWeight = $request->filled('min_weight') ? (float) $request->min_weight : null;
        $maxWeight = $request->filled('max_weight') ? (float) $request->max_weight : null;

        if ($request->filled('weight_range')) {
            switch ($request->weight_range) {
                case 'under_1g':
                    $minWeight = 0;
                    $maxWeight = 1.0;
                    break;
                case '1g_2g':
                    $minWeight = 1.0;
                    $maxWeight = 2.0;
                    break;
                case '2g_4g':
                    $minWeight = 2.0;
                    $maxWeight = 4.0;
                    break;
                case '2g_5g':
                    $minWeight = 2.0;
                    $maxWeight = 5.0;
                    break;
                case '4g_8g':
                    $minWeight = 4.0;
                    $maxWeight = 8.0;
                    break;
                case 'above_4g':
                    $minWeight = 4.0;
                    $maxWeight = null;
                    break;
                case 'above_8g':
                    $minWeight = 8.0;
                    $maxWeight = null;
                    break;
            }
        }

        if ($minWeight !== null) {
            $query->where('weight_grams', '>=', $minWeight);
        }
        if ($maxWeight !== null) {
            $query->where('weight_grams', '<=', $maxWeight);
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
            case 'price_desc':
                $direction = $request->get('sort') === 'price_asc' ? 'asc' : 'desc';
                $candidateProducts = (clone $query)->get();
                $sortedIds = $candidateProducts->sortBy(function ($prod) {
                    return $prod->calculated_price ?? (float) ($prod->base_price ?? 0);
                }, descending: ($direction === 'desc'))->pluck('id')->values()->all();

                if (!empty($sortedIds)) {
                    $orderString = implode(',', $sortedIds);
                    $query->orderByRaw("FIELD(id, {$orderString})");
                }
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
        } elseif ($request->has('metal_type')) {
            $metals = is_array($request->metal_type) ? $request->metal_type : explode(',', (string) $request->metal_type);
            $metals = array_filter(array_map('trim', $metals));
            if (!empty($metals)) {
                $title = implode(' & ', array_map('ucfirst', $metals)) . ' Jewellery';
            } else {
                $title = 'All Jewellery';
            }
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

        // Dynamic catalog bounds (cached for performance)
        $bounds = Cache::remember('shop_catalog_bounds', 300, function () {
            $allActive = Product::active()->get(['id', 'base_price', 'metal_type', 'purity', 'weight_grams']);
            $allPrices = $allActive->map(function ($p) {
                return $p->calculated_price ?? (float) ($p->base_price ?? 0);
            })->filter(fn($v) => $v > 0);

            $minPrice = $allPrices->isNotEmpty() ? (float) floor($allPrices->min() / 100) * 100 : 0;
            $maxPrice = $allPrices->isNotEmpty() ? (float) ceil($allPrices->max() / 1000) * 1000 : 100000;

            $minWeight = (float) ($allActive->min('weight_grams') ?? 0);
            $maxWeight = (float) ($allActive->max('weight_grams') ?? 20);

            return [
                'min_price'  => max(0, (int) $minPrice),
                'max_price'  => (int) $maxPrice,
                'min_weight' => max(0, floor($minWeight * 10) / 10),
                'max_weight' => ceil($maxWeight),
            ];
        });

        // Active Filter Count calculation
        $selectedMetals = array_filter(is_array($request->metal_type) ? $request->metal_type : ($request->filled('metal_type') ? explode(',', (string) $request->metal_type) : []));
        $selectedPurities = array_filter(is_array($request->purity) ? $request->purity : ($request->filled('purity') ? explode(',', (string) $request->purity) : []));
        $hasPriceFilter = $request->filled('price_range') || $request->filled('min_price') || $request->filled('max_price');
        $hasWeightFilter = $request->filled('weight_range') || $request->filled('min_weight') || $request->filled('max_weight');

        $activeFilterCount = 0;
        if ($request->filled('category')) $activeFilterCount++;
        $activeFilterCount += count($selectedMetals);
        $activeFilterCount += count($selectedPurities);
        if ($hasPriceFilter) $activeFilterCount++;
        if ($hasWeightFilter) $activeFilterCount++;
        if ($request->boolean('in_stock')) $activeFilterCount++;
        if ($request->filled('search')) $activeFilterCount++;
        if ($request->filled('vendor_id')) $activeFilterCount++;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'title'               => $title,
                'count'               => $count,
                'total_count'         => $products->total(),
                'active_filter_count' => $activeFilterCount,
                'showing'             => $showing,
                'active_filters_html' => view('shop.partials.active-filters', compact('products', 'selectedCategory', 'vendors'))->render(),
                'grid_html'           => view('shop.partials.products-grid', compact('products'))->render(),
                'pagination_html'     => $products->links()->render(),
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
            'isSubCategoryMode',
            'bounds',
            'activeFilterCount'
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
