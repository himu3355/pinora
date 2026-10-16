<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Services\PricingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleCalculationProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds 6 sample products covering the 4 jewellery pricing calculation types:
     * 1. Gold Jewellery (Gross 5g, Loss 0.3g = Net 4.7g, 22K, 12% Labour, 3% GST)
     * 2. Silver Jewellery (20g, 925 Purity, 10% Labour, 3% GST)
     * 3. Direct MRP Product (Fixed ₹800 final MRP, taxes included)
     * 4. Gold Rate Linked MRP Product (3g 24K, 6% Labour, 100 Pieces = Per-piece MRP)
     * 5. Gold Coin & Bar (1g 24K, 0.13% Labour, 3% GST with floor rounding)
     * 6. Silver Coin & Bar (10g 999 Fine Silver, 2% Labour, 3% GST with floor rounding)
     */
    public function run(): void
    {
        $vendor = Vendor::first() ?? Vendor::create([
            'store_name' => 'Pinora Heritage Artisans',
            'email' => 'artisan@pinora.com',
            'phone' => '9876543210',
            'status' => 'approved',
            'city' => 'Jaipur',
            'state' => 'RJ',
        ]);

        // Helper to find category
        $findCategory = function (string $slug, ?string $fallbackSlug = null): Category {
            return Category::where('slug', $slug)->first()
                ?? ($fallbackSlug ? Category::where('slug', $fallbackSlug)->first() : null)
                ?? Category::first();
        };

        $samples = [
            // ── 1. GOLD JEWELLERY ────────────────────────────────────────────
            [
                'name'                => '22K Gold Royal Heritage Bangle (Sample)',
                'slug'                => 'sample-22k-gold-royal-heritage-bangle',
                'category_slug'       => 'gold-jewellery-bangles',
                'fallback_slug'       => 'gold-jewellery',
                'product_type'        => 'jewellery',
                'metal_type'          => 'gold',
                'purity'              => '22K',
                'weight_grams'        => 5.000,
                'loss'                => 0.300, // Net weight = 4.700g
                'making_charges'      => 12.00,
                'making_charges_type' => 'percentage',
                'base_price'          => null,
                'mrp_pieces'          => 1,
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample 22K Gold Jewellery demonstrating Gross Weight (5.000g) − Less Weight (0.300g) = Net Weight (4.700g), 12% Labour, and 3% GST.',
                'description'         => '<p>Handcrafted royal heritage bangle crafted in 22K hallmarked gold. Features precise weight deductions: 5.000g gross weight, 0.300g less weight, billable net weight 4.700g, with 12% making charges and 3% GST.</p>',
                'image_metal'         => 'gold',
            ],

            // ── 2. SILVER JEWELLERY ──────────────────────────────────────────
            [
                'name'                => '925 Sterling Silver Royal Kada (Sample)',
                'slug'                => 'sample-925-sterling-silver-royal-kada',
                'category_slug'       => 'silver-jewellery-bracelets',
                'fallback_slug'       => 'silver-jewellery',
                'product_type'        => 'jewellery',
                'metal_type'          => 'silver',
                'purity'              => '925',
                'weight_grams'        => 20.000,
                'loss'                => 0.000, // Net weight = 20.000g
                'making_charges'      => 10.00,
                'making_charges_type' => 'percentage',
                'base_price'          => null,
                'mrp_pieces'          => 1,
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample Silver Jewellery demonstrating 20g 925 Sterling Silver, 10% Labour, and 3% GST.',
                'description'         => '<p>Classic heavy sterling silver kada crafted in 92.5% pure silver. Standard 20.000g net weight, 10% making charges on silver metal value, and 3% GST.</p>',
                'image_metal'         => 'silver',
            ],

            // ── 3A. DIRECT MRP PRODUCT ───────────────────────────────────────
            [
                'name'                => 'Luxury Jewellery Polishing & Care Kit (Sample Direct MRP)',
                'slug'                => 'sample-luxury-jewellery-care-kit-mrp',
                'category_slug'       => 'accessories-care-jewellery-care-cleaning',
                'fallback_slug'       => 'accessories-care',
                'product_type'        => 'mrp_fixed',
                'metal_type'          => 'other',
                'purity'              => null,
                'weight_grams'        => 0.150,
                'loss'                => 0.000,
                'making_charges'      => 0.00,
                'making_charges_type' => 'fixed',
                'base_price'          => 800.00, // Fixed MRP ₹800
                'mrp_pieces'          => 1,
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample Direct MRP product: Fixed consumer MRP of ₹800 (all taxes included, no metal or labour surcharges).',
                'description'         => '<p>Premium professional jewellery cleaning and sonic-dip solution with microfibre polishing cloth. Packaged for retail at a direct statutory MRP of ₹800 including GST.</p>',
                'image_metal'         => 'silver',
            ],

            // ── 3B. GOLD RATE LINKED MRP PRODUCT ─────────────────────────────
            [
                'name'                => '24K Pure Gold Nose Pin - Batch MRP (Sample)',
                'slug'                => 'sample-24k-gold-nose-pin-rate-linked-mrp',
                'category_slug'       => 'daily-wear',
                'fallback_slug'       => 'nose-pins',
                'product_type'        => 'mrp_linked',
                'metal_type'          => 'gold',
                'purity'              => '24K',
                'weight_grams'        => 3.000, // Batch weight 3g
                'loss'                => 0.000,
                'making_charges'      => 6.00,  // 6% labour
                'making_charges_type' => 'percentage',
                'base_price'          => null,
                'mrp_pieces'          => 100,   // Divided across 100 pieces
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample Gold Rate Linked MRP product: 3g 24K batch + 6% labour ÷ 100 pieces for dynamic per-piece MRP.',
                'description'         => '<p>Fine 24K pure gold delicate nose stud produced in production lots of 100 pieces from 3g total gold. Price automatically updates whenever daily gold rate changes: (3g × Gold Rate + 6% Labour) ÷ 100 pcs = Per Piece Final MRP.</p>',
                'image_metal'         => 'gold',
            ],

            // ── 4A. GOLD COIN & BAR ──────────────────────────────────────────
            [
                'name'                => '1g 24K 999 Fine Gold Minted Bar (Sample)',
                'slug'                => 'sample-1g-24k-fine-gold-minted-bar',
                'category_slug'       => 'coins-bars-gold-coins-bars',
                'fallback_slug'       => 'coins-bars',
                'product_type'        => 'coin_bar',
                'metal_type'          => 'gold',
                'purity'              => '24K',
                'weight_grams'        => 1.000,
                'loss'                => 0.000,
                'making_charges'      => 0.13,  // 0.13% minting charge
                'making_charges_type' => 'percentage',
                'base_price'          => null,
                'mrp_pieces'          => 1,
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample 24K 1g Fine Gold Minted Bar: 1g × 24K rate + 0.13% minting + 3% GST with floor rounding (decimals removed).',
                'description'         => '<p>Tamper-proof sealed 1 gram 24K (999.9) fine gold investment ingot. Follows bullion pricing formula with 0.13% making charge, 3% GST, and decimal floor rounding.</p>',
                'image_metal'         => 'gold',
            ],

            // ── 4B. SILVER COIN & BAR ────────────────────────────────────────
            [
                'name'                => '10g 999 Fine Silver Temple Ingot Bar (Sample)',
                'slug'                => 'sample-10g-999-fine-silver-temple-bar',
                'category_slug'       => 'coins-bars-silver-coins-bars',
                'fallback_slug'       => 'coins-bars',
                'product_type'        => 'coin_bar',
                'metal_type'          => 'silver',
                'purity'              => '999',
                'weight_grams'        => 10.000,
                'loss'                => 0.000,
                'making_charges'      => 2.00,  // 2% minting charge
                'making_charges_type' => 'percentage',
                'base_price'          => null,
                'mrp_pieces'          => 1,
                'discount_percent'    => 0.00,
                'short_description'   => 'Sample 10g 999 Fine Silver Minted Bar: 10g × Silver 999 rate + 2% minting + 3% GST with floor rounding.',
                'description'         => '<p>Finely minted 10 gram 999 pure silver devotional bar featuring Lakshmi-Ganesha motifs. Calculated with 2% minting charge, 3% GST, and floor rounding.</p>',
                'image_metal'         => 'silver',
            ],
        ];

        $pricingService = app(PricingService::class);

        foreach ($samples as $sample) {
            $cat = $findCategory($sample['category_slug'], $sample['fallback_slug']);

            $product = Product::updateOrCreate(
                ['slug' => $sample['slug']],
                [
                    'vendor_id'           => $vendor->id,
                    'category_id'         => $cat->id,
                    'name'                => $sample['name'],
                    'product_type'        => $sample['product_type'],
                    'metal_type'          => $sample['metal_type'],
                    'purity'              => $sample['purity'],
                    'weight_grams'        => $sample['weight_grams'],
                    'loss'                => $sample['loss'],
                    'making_charges'      => $sample['making_charges'],
                    'making_charges_type' => $sample['making_charges_type'],
                    'base_price'          => $sample['base_price'],
                    'mrp_pieces'          => $sample['mrp_pieces'],
                    'discount_percent'    => $sample['discount_percent'],
                    'short_description'   => $sample['short_description'],
                    'description'         => $sample['description'],
                    'status'              => 'active',
                    'is_featured'         => true,
                    'is_new_arrival'      => true,
                    'stock_quantity'      => 50,
                    'min_order_quantity'  => 1,
                    'certifications'      => ['bis_hallmark', 'certified_jewellery'],
                ]
            );

            // Ensure primary image exists
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'is_primary' => true,
                ],
                [
                    'path'       => 'products/placeholder_' . $sample['image_metal'] . '.png',
                    'alt_text'   => $product->name,
                    'sort_order' => 1,
                ]
            );

            // Create or update default variant
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'name'       => 'Standard',
                ],
                [
                    'sku'            => strtoupper(substr($sample['metal_type'], 0, 1) . '-SMPL-' . $product->id),
                    'weight_grams'   => $sample['weight_grams'],
                    'making_charges' => $sample['making_charges'],
                    'base_price'     => $sample['base_price'],
                    'stock_quantity' => 50,
                    'is_active'      => true,
                    'sort_order'     => 1,
                ]
            );

            // Log calculation breakdown
            $breakdown = $pricingService->calculate($product);
            $this->command->info(sprintf(
                "[%s] %s => Final Price: ₹%s (Net W: %sg, Making: %s, GST: ₹%s)",
                strtoupper($product->product_type),
                $product->name,
                number_format($breakdown['final_price'] ?? 0, 2),
                $breakdown['net_weight'] ?? $product->weight_grams,
                $breakdown['making_charges'] ?? 0,
                $breakdown['gst_amount'] ?? 0
            ));
        }
    }
}
