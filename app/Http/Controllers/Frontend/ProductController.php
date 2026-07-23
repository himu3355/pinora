<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\PricingService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'vendor',
                'category',
                'images' => fn($q) => $q->orderBy('sort_order'),
                'variants' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'reviews' => fn($q) => $q->where('status', 'approved')->with('user')->latest()->limit(20),
            ])
            ->firstOrFail();

        $pricing     = $this->pricing->calculate($product);
        $metalRate   = $pricing['metal_rate'] ?? null;

        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['primaryImage', 'vendor'])
            ->limit(4)
            ->get();

        $canReview = false;
        if (auth()->check()) {
            $canReview = auth()->user()
                ->orders()
                ->whereHas('items', fn($q) => $q->where('product_id', $product->id)->where('fulfillment_status', 'delivered'))
                ->exists()
                && !Review::where('product_id', $product->id)->where('user_id', auth()->id())->exists();
        }

        return view('shop.product', compact('product', 'pricing', 'metalRate', 'relatedProducts', 'canReview'));
    }

    public function storeReview(Request $request, int $id)
    {
        $product = Product::active()->findOrFail($id);

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'title'  => 'required|string|max:120',
            'body'   => 'required|string|max:2000',
        ]);

        Review::create([
            'product_id'          => $product->id,
            'user_id'             => auth()->id(),
            'rating'              => $validated['rating'],
            'title'               => $validated['title'],
            'body'                => $validated['body'],
            'status'              => 'pending',
            'is_verified_purchase'=> true,
        ]);

        return back()->with('success', 'Your review has been submitted and is pending approval. Thank you!');
    }
}
