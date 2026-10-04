<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class NosePinProductsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Parent Category: Nose Pins
        $parentCat = Category::firstOrCreate(
            ['slug' => 'nose-pins'],
            [
                'name' => 'Nose Pins',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // 2. Subcategories
        $subcategories = [
            'daily-wear' => ['name' => 'Daily Wear', 'sort_order' => 1],
            'floral'     => ['name' => 'Floral Designs', 'sort_order' => 2],
            'studded'    => ['name' => 'Studded', 'sort_order' => 3],
            'premium'    => ['name' => 'Premium Heritage', 'sort_order' => 4],
        ];

        $catMap = [];
        foreach ($subcategories as $slug => $data) {
            $cat = Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'parent_id' => $parentCat->id,
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ]
            );
            if ($cat->parent_id !== $parentCat->id) {
                $cat->update(['parent_id' => $parentCat->id, 'is_active' => true]);
            }
            $catMap[$slug] = $cat->id;
        }

        // 3. Resolve Vendor & Admin User
        $vendor = Vendor::where('store_slug', 'pinora')->first() ?? Vendor::first();
        $adminUser = User::first();

        // 4. Products List
        $products = [
            [
                'name' => 'Floral Gold Nose Pin',
                'slug' => 'floral-gold-nose-pin',
                'cat_slug' => 'floral',
                'sku' => 'NP-22K-FLR-001',
                'short_desc' => 'Exquisite 22K yellow gold floral nose pin with sparkling center stone and precision prong setting.',
                'desc' => "Crafted with pure 22K yellow gold, the Floral Gold Nose Pin celebrates the eternal elegance of nature. Featuring six meticulously cut petals adorned with brilliant American diamonds surrounding a radiant central core. Comes with an ultra-comfortable screw-wire back designed specifically for sensitive daily wear. 100% BIS hallmarked in Rajkot.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.450,
                'making_charges' => 1250.00,
                'stone_type' => 'CZ / American Diamond',
                'stone_weight_carats' => 0.080,
                'stone_quality' => 'AAA Grade',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-8450',
                'base_price' => 8450.00,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock_quantity' => 25,
                'images' => [
                    ['path' => 'products/nose-pins/pdp-main-1.jpg', 'is_primary' => true, 'alt' => 'Floral Gold Nose Pin Plinth View'],
                    ['path' => 'products/nose-pins/pdp-thumb-2.jpg', 'is_primary' => false, 'alt' => 'Floral Gold Nose Pin Side Profile'],
                    ['path' => 'products/nose-pins/pdp-thumb-3.jpg', 'is_primary' => false, 'alt' => 'Floral Gold Nose Pin Top Angle'],
                    ['path' => 'products/nose-pins/pdp-thumb-4.jpg', 'is_primary' => false, 'alt' => 'Floral Gold Nose Pin On Model'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Stunning shine and very comfortable fitting!',
                    'body' => 'I was looking for a daily wear 22K gold nose pin from Rajkot. The floral finish is breathtaking and does not hurt the nose piercing even overnight.'
                ]
            ],
            [
                'name' => 'Classic Diamond Nose Pin',
                'slug' => 'classic-diamond-nose-pin',
                'cat_slug' => 'studded',
                'sku' => 'NP-22K-DIA-002',
                'short_desc' => 'Timeless 22K gold solitaire nose pin with 6-prong brilliant-cut diamond solitaire.',
                'desc' => "A masterclass in understated royalty. The Classic Diamond Nose Pin features a round brilliant cut stone held in high-polish 22K gold prongs. The screw mechanism provides unmatched stability.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.520,
                'making_charges' => 1800.00,
                'stone_type' => 'Solitaire Diamond',
                'stone_weight_carats' => 0.100,
                'stone_quality' => 'VVS / EF',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-1290',
                'base_price' => 12900.00,
                'is_featured' => false,
                'is_new_arrival' => true,
                'stock_quantity' => 18,
                'images' => [
                    ['path' => 'products/nose-pins/prod-2.jpg', 'is_primary' => true, 'alt' => 'Classic Diamond Nose Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Pure luxury solitaire',
                    'body' => 'Catchy sparkle, exactly like real diamonds. Hallmarking verified on the BIS Care app.'
                ]
            ],
            [
                'name' => 'Ruby Teardrop Nose Pin',
                'slug' => 'ruby-teardrop-nose-pin',
                'cat_slug' => 'premium',
                'sku' => 'NP-22K-RBY-003',
                'short_desc' => 'Royal pear-cut crimson ruby enveloped in a diamond halo crafted in 22K gold.',
                'desc' => "Designed for grand celebrations and ethnic bridal attire. A deep crimson teardrop gem surrounded by micro-prong diamonds set in solid 22 karat gold.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.580,
                'making_charges' => 1600.00,
                'stone_type' => 'Crimson Ruby & CZ Halo',
                'stone_weight_carats' => 0.150,
                'stone_quality' => 'Fine Ruby',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-1025',
                'base_price' => 10250.00,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock_quantity' => 15,
                'images' => [
                    ['path' => 'products/nose-pins/prod-3.jpg', 'is_primary' => true, 'alt' => 'Ruby Teardrop Nose Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Rich color and traditional charm',
                    'body' => 'Pairs amazingly with silk sarees and festive lehengas. Received many compliments!'
                ]
            ],
            [
                'name' => 'Daily Wear Gold Stud',
                'slug' => 'daily-wear-gold-stud',
                'cat_slug' => 'daily-wear',
                'sku' => 'NP-22K-DLY-004',
                'short_desc' => 'Minimalist flower stud in solid 22K matte-and-gloss gold with center accent.',
                'desc' => "Crafted for women who adore subtle elegance. Super lightweight, feather-soft wire screw, hypoallergenic finish in 91.6% pure gold.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.380,
                'making_charges' => 950.00,
                'stone_type' => 'Accent CZ',
                'stone_weight_carats' => 0.020,
                'stone_quality' => 'AAA',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-5800',
                'base_price' => 5800.00,
                'is_featured' => false,
                'is_new_arrival' => false,
                'stock_quantity' => 30,
                'images' => [
                    ['path' => 'products/nose-pins/prod-4.jpg', 'is_primary' => true, 'alt' => 'Daily Wear Gold Stud'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Perfect for college and office wear',
                    'body' => 'Does not snag on dupattas or towels. The screw is sturdy and comfortable.'
                ]
            ],
            [
                'name' => 'Petal Bloom Nose Pin',
                'slug' => 'petal-bloom-nose-pin',
                'cat_slug' => 'floral',
                'sku' => 'NP-22K-PTL-005',
                'short_desc' => '5-petal blooming blossom design in pure 22K hallmarked gold with micro-pave stones.',
                'desc' => "An artistic ode to morning blooms. Five luminous diamond-cut petals reflect light at every turn, secured in a classic Gujarat goldsmith structure.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.480,
                'making_charges' => 1400.00,
                'stone_type' => 'Zircon Pave',
                'stone_weight_carats' => 0.070,
                'stone_quality' => 'AAA',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-9750',
                'base_price' => 9750.00,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock_quantity' => 20,
                'images' => [
                    ['path' => 'products/nose-pins/prod-5.jpg', 'is_primary' => true, 'alt' => 'Petal Bloom Nose Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Very delicate and elegant',
                    'body' => 'The size is just right — neither too small nor too big. Loved the luxury packaging.'
                ]
            ],
            [
                'name' => 'Emerald Halo Nose Pin',
                'slug' => 'emerald-halo-nose-pin',
                'cat_slug' => 'studded',
                'sku' => 'NP-22K-EMR-006',
                'short_desc' => 'Vibrant natural emerald gemstone encircled by radiant diamond flower prongs in 22K gold.',
                'desc' => "Intense forest emerald brilliance paired with 22K golden warmth. A standout statement piece inspired by royal Rajputana heritage.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.650,
                'making_charges' => 1900.00,
                'stone_type' => 'Emerald & CZ Halo',
                'stone_weight_carats' => 0.160,
                'stone_quality' => 'Fine Emerald',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-1420',
                'base_price' => 14200.00,
                'is_featured' => false,
                'is_new_arrival' => true,
                'stock_quantity' => 12,
                'images' => [
                    ['path' => 'products/nose-pins/prod-6.jpg', 'is_primary' => true, 'alt' => 'Emerald Halo Nose Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Unique green emerald beauty',
                    'body' => 'The emerald color is so vivid! The BIS Hallmark certificate was delivered along with it.'
                ]
            ],
            [
                'name' => 'Minimal Gold Hoop Pin',
                'slug' => 'minimal-gold-hoop-pin',
                'cat_slug' => 'daily-wear',
                'sku' => 'NP-22K-HOP-007',
                'short_desc' => 'Dainty 22K gold seamless nose ring hoop with single solitaire accent stone.',
                'desc' => "Modern circular minimalism for nose ring lovers. Snug circular 22K gold hoop wire with a single bezel-set stone for a subtle golden glint.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.420,
                'making_charges' => 1100.00,
                'stone_type' => 'Bezel Solitaire',
                'stone_weight_carats' => 0.030,
                'stone_quality' => 'AAA',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-6450',
                'base_price' => 6450.00,
                'is_featured' => false,
                'is_new_arrival' => false,
                'stock_quantity' => 22,
                'images' => [
                    ['path' => 'products/nose-pins/prod-7.jpg', 'is_primary' => true, 'alt' => 'Minimal Gold Hoop Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Comfortable hoop nose pin',
                    'body' => 'I usually find hoops difficult to wear, but this mechanism is seamless and painless.'
                ]
            ],
            [
                'name' => 'Royal Cluster Nose Pin',
                'slug' => 'royal-cluster-nose-pin',
                'cat_slug' => 'premium',
                'sku' => 'NP-22K-CLS-008',
                'short_desc' => 'Majestic multi-stone cluster nose pin with 9-stone diamond bloom in 22K gold.',
                'desc' => "The pinnacle of festive luxury. Nine brilliant gemstones arranged in a luminous solar cluster setting, designed to catch light from all perspectives.",
                'metal_type' => 'gold',
                'purity' => '22K',
                'weight_grams' => 0.780,
                'making_charges' => 2200.00,
                'stone_type' => '9-Stone Diamond Cluster',
                'stone_weight_carats' => 0.220,
                'stone_quality' => 'VVS / EF',
                'certification_type' => 'bis_hallmark',
                'certification_number' => 'BIS-HM-916-1750',
                'base_price' => 17500.00,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock_quantity' => 10,
                'images' => [
                    ['path' => 'products/nose-pins/prod-8.jpg', 'is_primary' => true, 'alt' => 'Royal Cluster Nose Pin'],
                ],
                'review' => [
                    'rating' => 5,
                    'title' => 'Absolute royal masterpiece',
                    'body' => 'Looks extremely rich. The weight and gold purity are top-notch. High quality!'
                ]
            ]
        ];

        foreach ($products as $p) {
            $catId = $catMap[$p['cat_slug']];

            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'category_id' => $catId,
                    'vendor_id' => $vendor->id,
                    'created_by' => $adminUser?->id,
                    'description' => $p['desc'],
                    'short_description' => $p['short_desc'],
                    'sku' => $p['sku'],
                    'metal_type' => $p['metal_type'],
                    'purity' => $p['purity'],
                    'weight_grams' => $p['weight_grams'],
                    'making_charges' => $p['making_charges'],
                    'making_charges_type' => 'fixed',
                    'stone_type' => $p['stone_type'],
                    'stone_weight_carats' => $p['stone_weight_carats'],
                    'stone_quality' => $p['stone_quality'],
                    'certification_type' => $p['certification_type'],
                    'certification_number' => $p['certification_number'],
                    'base_price' => $p['base_price'],
                    'is_featured' => $p['is_featured'],
                    'is_new_arrival' => $p['is_new_arrival'],
                    'stock_quantity' => $p['stock_quantity'],
                    'status' => 'active',
                ]
            );

            // Re-sync images
            $product->images()->delete();
            foreach ($p['images'] as $idx => $img) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $img['path'],
                    'alt_text' => $img['alt'],
                    'is_primary' => $img['is_primary'],
                    'sort_order' => $idx,
                ]);
            }

            // Re-sync variants
            $product->variants()->delete();
            foreach (['Small', 'Medium', 'Large'] as $vIdx => $vName) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $vName,
                    'sku' => $p['sku'] . '-' . strtoupper(substr($vName, 0, 1)),
                    'stock_quantity' => 10,
                    'sort_order' => $vIdx,
                    'is_active' => true,
                ]);
            }

            // Sync sample review
            if (!empty($p['review']) && $adminUser) {
                Review::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'user_id' => $adminUser->id,
                    ],
                    [
                        'rating' => $p['review']['rating'],
                        'title' => $p['review']['title'],
                        'body' => $p['review']['body'],
                        'status' => 'approved',
                        'is_verified_purchase' => true,
                    ]
                );
            }
        }
    }
}
