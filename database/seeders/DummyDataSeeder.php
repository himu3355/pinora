<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use App\Models\Category;
use App\Models\MetalRate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Call Category and MetalRate Seeders first to guarantee basic catalog structures exist
            $this->call([
                CategorySeeder::class,
                MetalRateSeeder::class,
            ]);

            // 2. Fetch metal rates for pricing calculation
            $gold22K = MetalRate::getLatestRate('gold', '22K');
            $gold18K = MetalRate::getLatestRate('gold', '18K');
            $silver925 = MetalRate::getLatestRate('silver', '925');

            // 3. Seed Users
            // Super Admin
            $admin = User::firstOrCreate(['email' => 'admin@pinora.com'], [
                'name' => 'Pinora Admin',
                'password' => bcrypt('password'),
                'phone' => '9999999999',
                'status' => 'active'
            ]);
            $admin->assignRole('super_admin');

            // Customers
            $customer1 = User::firstOrCreate(['email' => 'customer1@pinora.com'], [
                'name' => 'Aarav Mehta',
                'password' => bcrypt('password'),
                'phone' => '9876543210',
                'status' => 'active'
            ]);
            $customer1->assignRole('customer');

            $customer2 = User::firstOrCreate(['email' => 'customer2@pinora.com'], [
                'name' => 'Diya Sharma',
                'password' => bcrypt('password'),
                'phone' => '9876543211',
                'status' => 'active'
            ]);
            $customer2->assignRole('customer');

            // Saved addresses
            Address::firstOrCreate([
                'user_id' => $customer1->id,
                'label' => 'Home'
            ], [
                'type' => 'both',
                'full_name' => 'Aarav Mehta',
                'phone' => '9876543210',
                'address_line_1' => 'Flat 402, Royal Residency',
                'address_line_2' => 'MG Road',
                'city' => 'Mumbai',
                'state' => 'MH',
                'pincode' => '400001',
                'country' => 'India',
                'is_default' => true
            ]);

            Address::firstOrCreate([
                'user_id' => $customer2->id,
                'label' => 'Office'
            ], [
                'type' => 'both',
                'full_name' => 'Diya Sharma',
                'phone' => '9876543211',
                'address_line_1' => 'Tech Park Sector 5',
                'address_line_2' => 'Salt Lake',
                'city' => 'Kolkata',
                'state' => 'WB',
                'pincode' => '700091',
                'country' => 'India',
                'is_default' => true
            ]);

            // 4. Seed Vendors
            $vendorNames = [
                'Zaveri & Sons' => 'zaveri@pinora.com',
                'Kalyan Artisans' => 'kalyan@pinora.com',
                'Tanishq Heritage' => 'tanishq@pinora.com',
            ];

            $vendors = [];
            foreach ($vendorNames as $storeName => $email) {
                $user = User::firstOrCreate(['email' => $email], [
                    'name' => $storeName . ' Owner',
                    'password' => bcrypt('password'),
                    'phone' => '90000000' . rand(10, 99),
                    'status' => 'active'
                ]);
                $user->assignRole('vendor');

                $vendor = Vendor::firstOrCreate(['user_id' => $user->id], [
                    'store_name' => $storeName,
                    'description' => 'Fine handcrafted jewellery from ' . $storeName . '.',
                    'phone' => $user->phone,
                    'email' => $email,
                    'address' => 'Artisan Street ' . rand(1, 100),
                    'city' => 'Jaipur',
                    'state' => 'RJ',
                    'pincode' => '302001',
                    'gst_number' => '08AAAAA' . rand(1000, 9999) . 'A1Z' . rand(0, 9),
                    'pan_number' => 'ABCDE' . rand(1000, 9999) . 'F',
                    'bank_name' => 'State Bank of India',
                    'bank_account_name' => $storeName,
                    'bank_account_number' => '30010045' . rand(1000, 9999),
                    'bank_ifsc_code' => 'SBIN0000101',
                    'status' => 'approved'
                ]);

                // Create a sample document
                VendorDocument::firstOrCreate([
                    'vendor_id' => $vendor->id,
                    'type' => 'gst_certificate',
                ], [
                    'file_path' => 'documents/' . $vendor->id . '/gst_certificate.pdf',
                    'original_name' => 'gst_certificate.pdf',
                    'status' => 'verified',
                    'verified_at' => now(),
                    'notes' => 'Pre-approved via seeder'
                ]);

                $vendors[] = $vendor;
            }

            // 5. Seed Products & Variants
            $productMockData = [
                ['name' => 'Royal Kundan Gold Necklace', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Necklaces', 'weight' => 28.50, 'making' => 450.00],
                ['name' => 'Sleek 18K Diamond Ring', 'metal' => 'gold', 'purity' => '18K', 'category' => 'Rings', 'weight' => 4.20, 'making' => 600.00],
                ['name' => 'Art Deco Gold Jhumkas', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Earrings', 'weight' => 12.80, 'making' => 500.00],
                ['name' => 'Sterling Silver Kada', 'metal' => 'silver', 'purity' => '925', 'category' => 'Bangles', 'weight' => 24.00, 'making' => 80.00],
                ['name' => 'Classic Gold Curb Chain', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Chains', 'weight' => 15.00, 'making' => 300.00],
            ];

            $products = [];
            foreach ($productMockData as $k => $mock) {
                $vendor = $vendors[$k % count($vendors)];
                $category = Category::where('name', $mock['category'])->first();

                if (!$category) {
                    continue;
                }

                $product = Product::firstOrCreate(['slug' => Str::slug($mock['name'] . '-' . $vendor->id)], [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'name' => $mock['name'],
                    'metal_type' => $mock['metal'],
                    'purity' => $mock['purity'],
                    'weight_grams' => $mock['weight'],
                    'making_charges' => $mock['making'],
                    'making_charges_type' => 'per_gram',
                    'description' => 'Beautifully designed premium ' . $mock['name'] . ' crafted by ' . $vendor->store_name . '.',
                    'status' => 'active',
                    'is_featured' => ($k % 2 === 0),
                    'is_new_arrival' => true,
                    'stock_quantity' => 10,
                ]);

                // Seed product variants
                if ($mock['category'] === 'Rings') {
                    foreach ([12, 14, 16] as $size) {
                        ProductVariant::firstOrCreate([
                            'product_id' => $product->id,
                            'name' => 'Size ' . $size,
                        ], [
                            'sku' => strtoupper(substr($mock['metal'], 0, 1) . '-' . $product->id . '-' . $size),
                            'weight_grams' => $mock['weight'] + ($size - 14) * 0.2,
                            'making_charges' => $mock['making'],
                            'stock_quantity' => 5,
                            'is_active' => true,
                            'sort_order' => $size,
                        ]);
                    }
                } else {
                    ProductVariant::firstOrCreate([
                        'product_id' => $product->id,
                        'name' => 'Standard',
                    ], [
                        'sku' => strtoupper(substr($mock['metal'], 0, 1) . '-' . $product->id . '-STD'),
                        'weight_grams' => $mock['weight'],
                        'making_charges' => $mock['making'],
                        'stock_quantity' => 10,
                        'is_active' => true,
                        'sort_order' => 1,
                    ]);
                }

                // Primary Image
                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'is_primary' => true,
                ], [
                    'path' => 'products/placeholder_' . $mock['metal'] . '.png',
                    'alt_text' => $product->name,
                    'sort_order' => 1,
                ]);

                $products[] = $product;
            }

            // 6. Seed a completed order transaction
            $orderProduct = $products[0]; // Kundan Necklace (Gold 22K, weight 28.5)
            $variant = $orderProduct->variants()->first();

            // Calculate price details
            $rateValue = $gold22K ? (float) $gold22K->rate_per_gram : 6600.00;
            $weight = (float) $variant->weight_grams;
            $metalCost = $weight * $rateValue;
            $makingCharges = $weight * (float) $orderProduct->making_charges;
            $subtotal = $metalCost + $makingCharges;
            $gst = $subtotal * 0.03;
            $total = $subtotal + $gst;

            $order = Order::firstOrCreate(['order_number' => 'PIN-' . today()->format('Y') . '-00001'], [
                'user_id' => $customer1->id,
                'guest_email' => null,
                'guest_phone' => null,
                'status' => 'delivered',
                'shipping_name' => $customer1->name,
                'shipping_phone' => '9876543210',
                'shipping_address_line_1' => 'Flat 402, Royal Residency',
                'shipping_address_line_2' => 'MG Road',
                'shipping_city' => 'Mumbai',
                'shipping_state' => 'MH',
                'shipping_pincode' => '400001',
                'shipping_country' => 'India',
                'subtotal' => $subtotal,
                'discount_amount' => 0.00,
                // Intra-state (Customer MH, Jaipur Vendor RJ -> Wait, Jaipur is RJ, Customer is MH. That is inter-state! So IGST is 3%, CGST/SGST is 0!)
                'cgst_amount' => 0.00,
                'sgst_amount' => 0.00,
                'igst_amount' => $gst,
                'total_amount' => $total,
                'payment_method' => 'razorpay',
                'payment_status' => 'paid',
                'payment_reference' => 'pay_mock1234567',
                'paid_at' => now(),
                'notes' => 'Seeded test order'
            ]);

            $orderItem = OrderItem::firstOrCreate([
                'order_id' => $order->id,
                'product_id' => $orderProduct->id,
            ], [
                'vendor_id' => $orderProduct->vendor_id,
                'product_variant_id' => $variant->id,
                'product_name' => $orderProduct->name,
                'product_sku' => $variant->sku,
                'variant_name' => $variant->name,
                'metal_type' => $orderProduct->metal_type,
                'purity' => $orderProduct->purity,
                'weight_grams' => $weight,
                'metal_rate_used' => $rateValue,
                'making_charges' => $orderProduct->making_charges,
                'quantity' => 1,
                'unit_price' => $subtotal,
                'subtotal' => $subtotal,
                'cgst_rate' => 0.00,
                'sgst_rate' => 0.00,
                'igst_rate' => 3.00,
                'cgst_amount' => 0.00,
                'sgst_amount' => 0.00,
                'igst_amount' => $gst,
                'total_price' => $total,
                'fulfillment_status' => 'delivered',
                'shipped_at' => now()->subDay(),
                'delivered_at' => now(),
            ]);

            // 7. Seed Payout record
            $payout = Payout::firstOrCreate([
                'vendor_id' => $orderProduct->vendor_id,
                'payout_reference' => 'PAY-' . today()->format('Y') . '-00001',
            ], [
                'period_from' => today()->subDays(7),
                'period_to' => today(),
                'total_orders_amount' => $subtotal,
                'total_vendor_earnings' => $subtotal,
                'adjustments' => 0.00,
                'final_payout_amount' => $subtotal,
                'bank_account_name' => $orderProduct->vendor->bank_account_name,
                'bank_account_number' => $orderProduct->vendor->bank_account_number,
                'bank_ifsc_code' => $orderProduct->vendor->bank_ifsc_code,
                'bank_name' => $orderProduct->vendor->bank_name,
                'status' => 'draft',
            ]);

            // 9. Seed product review
            Review::firstOrCreate([
                'product_id' => $orderProduct->id,
                'user_id' => $customer1->id,
            ], [
                'order_item_id' => $orderItem->id,
                'rating' => 5,
                'title' => 'Exquisite Craftsmanship',
                'body' => 'Absolutely gorgeous piece! The gold quality and hand craftsmanship is exceptional. Strongly recommend this seller.',
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'is_verified_purchase' => true,
            ]);
        });
    }
}
