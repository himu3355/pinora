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

        $variants = $product->variants;
        $variantData = [];
        $selectedVariant = null;

        if ($variants->isNotEmpty()) {
            foreach ($variants as $v) {
                $vPricing = $this->pricing->calculate($product, $v->id);
                $grossWeight = (float) ($v->weight_grams ?? $product->weight_grams ?? 0);
                $loss = (float) ($product->loss ?? 0);
                $netWeight = max(0, $grossWeight - $loss);

                $variantData[$v->id] = [
                    'id'               => $v->id,
                    'name'             => $v->name,
                    'sku'              => $v->sku ?? $product->sku,
                    'stock_quantity'   => (int) $v->stock_quantity,
                    'gross_weight'     => number_format($grossWeight, 3),
                    'loss'             => number_format($loss, 3),
                    'net_weight'       => number_format($netWeight, 3),
                    'final_price'      => (float) ($vPricing['final_price'] ?? 0),
                    'formatted_price'  => '₹' . number_format($vPricing['final_price'] ?? 0, 0),
                    'metal_cost'       => (float) ($vPricing['metal_cost'] ?? 0),
                    'formatted_metal'  => '₹' . number_format($vPricing['metal_cost'] ?? 0, 2),
                    'making_charges'   => (float) ($vPricing['making_charges'] ?? 0),
                    'formatted_making' => '₹' . number_format($vPricing['making_charges'] ?? 0, 2),
                    'gst_amount'       => (float) ($vPricing['gst_amount'] ?? 0),
                    'formatted_gst'    => '₹' . number_format($vPricing['gst_amount'] ?? 0, 2),
                ];
            }

            $selectedVariant = $variants->firstWhere('stock_quantity', '>', 0) ?? $variants->first();
            if ($selectedVariant) {
                $pricing = $this->pricing->calculate($product, $selectedVariant->id);
            }
        } else {
            $pricing = $this->pricing->calculate($product);
        }

        $metalRate = $pricing['metal_rate'] ?? null;

        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['primaryImage', 'vendor'])
            ->limit(4)
            ->get();

        if ($relatedProducts->count() < 4) {
            $excludeIds = $relatedProducts->pluck('id')->push($product->id)->all();
            $additional = Product::active()
                ->whereNotIn('id', $excludeIds)
                ->with(['primaryImage', 'vendor'])
                ->limit(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($additional);
        }

        $canReview = false;
        if (auth()->check()) {
            $canReview = auth()->user()
                ->orders()
                ->whereHas('items', fn($q) => $q->where('product_id', $product->id)->where('fulfillment_status', 'delivered'))
                ->exists()
                && !Review::where('product_id', $product->id)->where('user_id', auth()->id())->exists();
        }

        return view('shop.product', compact('product', 'pricing', 'metalRate', 'relatedProducts', 'canReview', 'variants', 'variantData', 'selectedVariant'));
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
