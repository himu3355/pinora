# Step 44: Shop Page — Product Listings with Filters

**Goal:** Create the shop/product listing page with sidebar filters, sorting, and pagination, supporting fully dynamic AJAX updates and History API URL synchronization.
**Depends On:** Step 43 (Layout, routes), Step 16 (Product model), Step 51 (PricingService)
**Next Step:** Step 45 (Product Detail)

---

## Files Created/Modified

- `app/Http/Controllers/Frontend/ShopController.php` [Modified]
- `resources/views/shop/index.blade.php` [Modified]
- `resources/views/shop/partials/active-filters.blade.php` [New]
- `resources/views/shop/partials/products-grid.blade.php` [New]
- `resources/js/app.js` [Modified]
- Routes added to `routes/web.php` [Unchanged]

---

## 1. Routes — `routes/web.php`

```php
// Shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/category/{slug}', [ShopController::class, 'byCategory'])->name('shop.category');
Route::get('/vendor/{slug}/shop', [ShopController::class, 'byVendor'])->name('shop.vendor');
```

---

## 2. `app/Http/Controllers/Frontend/ShopController.php`

```php
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

        $products   = $query->paginate(24)->withQueryString();
        $categories = Category::active()->topLevel()->with('children')->get();
        $vendors    = Vendor::approved()->orderBy('store_name')->get(['id', 'store_name']);

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

        return view('shop.index', compact('products', 'categories', 'vendors', 'selectedCategory'));
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
```

---

## 3. `resources/views/shop/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Shop — Pinora Jewellery')
@section('meta_description', 'Browse certified gold, silver and diamond jewellery from verified vendors.')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-12">

    {{-- Page Header --}}
    <div class="mb-10">
        <h1 id="shop-title" class="font-primary text-4xl font-normal mb-2 text-text-light">
            @if(request('search'))
                Search: "{{ request('search') }}"
            @elseif(isset($selectedCategory) && $selectedCategory)
                {{ $selectedCategory->name }}
            @elseif(request('metal_type'))
                {{ ucfirst(request('metal_type')) }} Jewellery
            @else
                All Jewellery
            @endif
        </h1>
        <p id="shop-count" class="text-text-muted text-[0.9rem]">
            {{ $products->total() }} {{ Str::plural('product', $products->total()) }} found
        </p>
    </div>

    {{-- Active Filters --}}
    <div id="active-filters-container">
        @include('shop.partials.active-filters')
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">

        {{-- ===== SIDEBAR FILTERS ===== --}}
        <aside class="bg-dark-card border border-border-gold rounded-lg p-6 lg:sticky lg:top-[90px]">
            <h3 class="font-primary text-xl mb-6 pb-3 border-b border-border-gold text-text-light">Filter</h3>

            <form method="GET" action="{{ route('shop.index') }}" id="filter-form">
                {{-- Categories --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Category</h4>
                    @foreach($categories as $cat)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }} class="accent-gold">
                        {{ $cat->name }}
                    </label>
                    @if($cat->children->isNotEmpty())
                        @foreach($cat->children as $child)
                        <label class="flex items-center gap-2 mb-2 pl-4 cursor-pointer text-[0.8rem] text-text-muted hover:text-gold transition-colors">
                            <input type="radio" name="category" value="{{ $child->slug }}" {{ request('category') === $child->slug ? 'checked' : '' }} class="accent-gold">
                            {{ $child->name }}
                        </label>
                        @endforeach
                    @endif
                    @endforeach
                </div>

                {{-- Metal Type --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Metal Type</h4>
                    @foreach(['gold'=>'Gold','silver'=>'Silver','platinum'=>'Platinum','other'=>'Other'] as $val => $label)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="metal_type" value="{{ $val }}" {{ request('metal_type') === $val ? 'checked' : '' }} class="accent-gold">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                {{-- Purity --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Purity</h4>
                    @foreach(['24K','22K','18K','14K','999','925','950'] as $purity)
                    <label class="flex items-center gap-2 mb-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="radio" name="purity" value="{{ $purity }}" {{ request('purity') === $purity ? 'checked' : '' }} class="accent-gold">
                        {{ $purity }}
                    </label>
                    @endforeach
                </div>

                {{-- In Stock --}}
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-text-muted hover:text-gold transition-colors">
                        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} class="accent-gold">
                        In Stock Only
                    </label>
                </div>

                {{-- Vendor --}}
                <div class="mb-6">
                    <h4 class="text-xs tracking-wider uppercase text-gold mb-4 font-semibold">Vendor</h4>
                    <select name="vendor_id" class="w-full bg-white/5 border border-border-gold rounded-lg py-2 px-3 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('shop.index') }}" class="btn btn-outline-gold w-full justify-center mt-2 data-filter-clear">Clear Filters</a>
            </form>
        </aside>

        {{-- ===== PRODUCT GRID ===== --}}
        <div>

            {{-- Sort Bar --}}
            <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                <span id="showing-results-text" class="text-sm text-text-muted">
                    @if($products->total() > 0)
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
                    @else
                        Showing 0–0 of 0
                    @endif
                </span>
                <form method="GET" action="{{ route('shop.index') }}" id="sort-form">
                    <select name="sort" class="bg-dark-card border border-border-gold rounded-lg py-2 px-4 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
                        <option value="newest" {{ request('sort','newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                    </select>
                </form>
            </div>

            {{-- Products --}}
            <div id="products-grid-container" class="transition-opacity duration-200">
                @include('shop.partials.products-grid')
            </div>

            {{-- Pagination --}}
            <div id="pagination-container" class="mt-12">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
```

---

## 4. `resources/views/shop/partials/active-filters.blade.php`

```blade
@if(request()->hasAny(['category','metal_type','purity','vendor_id','search','in_stock']))
<div class="flex flex-wrap gap-2 mb-6 items-center">
    <span class="text-[0.8rem] text-text-muted">Active filters:</span>
    
    @if(request('category') && isset($selectedCategory) && $selectedCategory)
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Category: {{ $selectedCategory->name }}
            <a href="{{ request()->fullUrlWithoutQuery(['category']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="category">&times;</a>
        </span>
    @endif

    @if(request('metal_type'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Metal: {{ ucfirst(request('metal_type')) }}
            <a href="{{ request()->fullUrlWithoutQuery(['metal_type']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="metal_type">&times;</a>
        </span>
    @endif

    @if(request('purity'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Purity: {{ request('purity') }}
            <a href="{{ request()->fullUrlWithoutQuery(['purity']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="purity">&times;</a>
        </span>
    @endif

    @if(request('vendor_id'))
        @php
            $vName = $vendors->firstWhere('id', request('vendor_id'))?->store_name ?? 'Vendor';
        @endphp
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Vendor: {{ $vName }}
            <a href="{{ request()->fullUrlWithoutQuery(['vendor_id']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="vendor_id">&times;</a>
        </span>
    @endif

    @if(request('in_stock'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            In Stock Only
            <a href="{{ request()->fullUrlWithoutQuery(['in_stock']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="in_stock">&times;</a>
        </span>
    @endif

    @if(request('search'))
        <span class="inline-flex items-center gap-2 py-1.5 px-3 bg-gold/10 border border-border-gold rounded-full text-[0.78rem] text-gold">
            Search: "{{ request('search') }}"
            <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="text-gold font-bold hover:text-gold-light data-filter-remove" data-filter-key="search">&times;</a>
        </span>
    @endif

    <a href="{{ route('shop.index') }}" class="text-[0.78rem] text-text-muted hover:text-gold data-filter-clear">Clear all</a>
</div>
@endif
```

---

## 5. `resources/views/shop/partials/products-grid.blade.php`

```blade
@if($products->isEmpty())
    <div class="text-center py-20 px-4 text-text-muted">
        <div class="text-5xl mb-4">💎</div>
        <h3 class="font-primary text-2xl mb-2 text-text-light">No products found</h3>
        <p>Try adjusting your filters or <a href="{{ route('shop.index') }}" class="text-gold hover:underline">clear all</a>.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($products as $product)
            @include('partials.product-card', ['product' => $product])
        @endforeach
    </div>
@endif
```

---

## Notes

- **AJAX Updates**: All filters, sort selections, pagination page links, and active tag removals intercept standard requests and perform a `fetch` query, retrieving JSON parts to swap in specific containers.
- **Visual Feedback**: The product grid uses an opacity fade effect (`opacity-50`) while loading dynamic results.
- **History Synchronization**: The History API (`history.pushState`) synchronizes queries in the address bar. The window `popstate` event listener enables back and forward button navigation.
- **Paginator**: Dynamic pagination links bind event listeners via event delegation to intercept normal navigations.
- **Wishlist Action delegation**: Refactored the wishlist toggle trigger click listener in `app.js` using event delegation to support newly fetched dynamic product cards.
