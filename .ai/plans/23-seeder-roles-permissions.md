# Step 23: Seed Roles & Permissions

## Goal
Create the `RolesAndPermissionsSeeder` that bootstraps the default roles and their associated permissions using Spatie Laravel Permission. Also configure `Gate::before` in `AppServiceProvider` so that `super_admin` bypasses all policy checks automatically.

---

## Files to Create / Modify

| Action | File |
|--------|------|
| **Create** | `database/seeders/RolesAndPermissionsSeeder.php` |
| **Create** | `database/seeders/DummyDataSeeder.php` |
| **Modify** | `database/seeders/DatabaseSeeder.php` |
| **Modify** | `app/Providers/AppServiceProvider.php` |

---

## Permission Groups

| Group | Permissions |
|-------|-------------|
| Vendor Management | `view_vendor`, `create_vendor`, `edit_vendor`, `approve_vendor`, `suspend_vendor`, `delete_vendor` |
| Product Management | `view_product`, `create_product`, `edit_product`, `delete_product`, `publish_product` |
| Category Management | `view_category`, `create_category`, `edit_category`, `delete_category` |
| Metal Rate Management | `view_metal_rate`, `create_metal_rate`, `edit_metal_rate`, `delete_metal_rate` |
| Order Management | `view_order`, `update_order_status`, `cancel_order` |
| Customer Management | `view_customer`, `edit_customer`, `tag_customer`, `impersonate_customer` |
| Review Management | `view_review`, `approve_review`, `reject_review`, `delete_review` |
| Commission Management | `view_commission`, `create_payout`, `mark_payout_paid` |
| User Management | `view_user`, `create_user`, `edit_user`, `delete_user` |
| Role Management | `view_role`, `create_role`, `edit_role`, `delete_role` |

---

## Role → Permission Matrix

| Role | Permissions Granted |
|------|---------------------|
| `super_admin` | All (via `Gate::before` bypass) |
| `platform_manager` | All **except** role management permissions |
| `vendor` | `view_product`, `create_product`, `edit_product`, `delete_product`, `publish_product`, `view_order`, `update_order_status`, `view_commission` |
| `vendor_staff` | `view_product`, `edit_product`, `view_order`, `update_order_status` |
| `customer` | *(none — frontend only)* |

---

## PHP Code

### `database/seeders/RolesAndPermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // ─────────────────────────────────────────────
        // Define all permissions grouped by domain
        // ─────────────────────────────────────────────
        $permissions = [
            // Vendor Management
            'view_vendor',
            'create_vendor',
            'edit_vendor',
            'approve_vendor',
            'suspend_vendor',
            'delete_vendor',

            // Product Management
            'view_product',
            'create_product',
            'edit_product',
            'delete_product',
            'publish_product',

            // Category Management
            'view_category',
            'create_category',
            'edit_category',
            'delete_category',

            // Metal Rate Management
            'view_metal_rate',
            'create_metal_rate',
            'edit_metal_rate',
            'delete_metal_rate',

            // Order Management
            'view_order',
            'update_order_status',
            'cancel_order',

            // Customer Management
            'view_customer',
            'edit_customer',
            'tag_customer',
            'impersonate_customer',

            // Review Management
            'view_review',
            'approve_review',
            'reject_review',
            'delete_review',

            // Commission Management
            'view_commission',
            'create_payout',
            'mark_payout_paid',

            // User Management
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',

            // Role Management
            'view_role',
            'create_role',
            'edit_role',
            'delete_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => $guard]
            );
        }

        // ─────────────────────────────────────────────
        // Create Roles
        // ─────────────────────────────────────────────

        // super_admin — bypass handled by Gate::before
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => $guard]
        );
        // No explicit permissions needed; Gate::before grants everything.

        // platform_manager — full access except role management
        $platformManager = Role::firstOrCreate(
            ['name' => 'platform_manager', 'guard_name' => $guard]
        );
        $platformManagerPermissions = array_filter(
            $permissions,
            fn(string $p) => !in_array($p, ['view_role', 'create_role', 'edit_role', 'delete_role'])
        );
        $platformManager->syncPermissions($platformManagerPermissions);

        // vendor — own product/order/commission management
        $vendor = Role::firstOrCreate(
            ['name' => 'vendor', 'guard_name' => $guard]
        );
        $vendor->syncPermissions([
            'view_product',
            'create_product',
            'edit_product',
            'delete_product',
            'publish_product',
            'view_order',
            'update_order_status',
            'view_commission',
        ]);

        // vendor_staff — limited vendor access
        $vendorStaff = Role::firstOrCreate(
            ['name' => 'vendor_staff', 'guard_name' => $guard]
        );
        $vendorStaff->syncPermissions([
            'view_product',
            'edit_product',
            'view_order',
            'update_order_status',
        ]);

        // customer — no admin/vendor access
        $customer = Role::firstOrCreate(
            ['name' => 'customer', 'guard_name' => $guard]
        );
        $customer->syncPermissions([]);

        $this->command->info('✅ Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permission Count'],
            [
                ['super_admin', 'ALL (Gate::before bypass)'],
                ['platform_manager', count($platformManagerPermissions)],
                ['vendor', 8],
                ['vendor_staff', 4],
                ['customer', 0],
            ]
        );
    }
}
```

---

### `database/seeders/DatabaseSeeder.php` (updated `run` method)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
```

### `database/seeders/DummyDataSeeder.php`

```php
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
use App\Models\Commission;
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
            // 1. Seed Metal Rates
            $rates = [
                ['metal_type' => 'gold', 'purity' => '24K', 'rate_per_gram' => 7200.00],
                ['metal_type' => 'gold', 'purity' => '22K', 'rate_per_gram' => 6600.00],
                ['metal_type' => 'gold', 'purity' => '18K', 'rate_per_gram' => 5400.00],
                ['metal_type' => 'gold', 'purity' => '14K', 'rate_per_gram' => 4200.00],
                ['metal_type' => 'silver', 'purity' => '999', 'rate_per_gram' => 90.00],
                ['metal_type' => 'silver', 'purity' => '925', 'rate_per_gram' => 83.25],
                ['metal_type' => 'platinum', 'purity' => '950', 'rate_per_gram' => 3800.00],
            ];
            foreach ($rates as $r) {
                MetalRate::updateOrCreate([
                    'metal_type' => $r['metal_type'],
                    'purity' => $r['purity'],
                    'effective_date' => today()->toDateString()
                ], [
                    'rate_per_gram' => $r['rate_per_gram'],
                    'notes' => 'Seeded default rate'
                ]);
            }

            // 2. Seed Test Users
            // Super Admin
            $admin = User::firstOrCreate(['email' => 'admin@pinora.com'], [
                'name' => 'Pinora Admin',
                'password' => bcrypt('password'),
                'phone' => '9999999999',
                'status' => 'active'
            ]);
            $admin->assignRole('super_admin');

            // 2 Customers
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

            // Save dummy addresses for customers
            Address::firstOrCreate(['user_id' => $customer1->id], [
                'name' => 'Aarav Mehta (Home)',
                'phone' => '9876543210',
                'address_line_1' => 'Flat 402, Royal Residency',
                'address_line_2' => 'MG Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
                'is_default' => true
            ]);

            Address::firstOrCreate(['user_id' => $customer2->id], [
                'name' => 'Diya Sharma (Office)',
                'phone' => '9876543211',
                'address_line_1' => 'Tech Park Sector 5',
                'address_line_2' => 'Salt Lake',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
                'pincode' => '700091',
                'is_default' => true
            ]);

            // 3. Seed 5 Vendors
            $vendorNames = [
                'Zaveri & Sons' => 'zaveri@pinora.com',
                'Kalyan Artisans' => 'kalyan@pinora.com',
                'Tanishq Heritage' => 'tanishq@pinora.com',
                'Bhima Jewellery' => 'bhima@pinora.com',
                'Malabar Gold & Silver' => 'malabar@pinora.com'
            ];

            $vendors = [];
            foreach ($vendorNames as $storeName => $email) {
                $user = User::firstOrCreate(['email' => $email], [
                    'name' => $storeName . ' Owner',
                    'password' => bcrypt('password'),
                    'phone' => '900000000' . rand(0, 9),
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
                    'state' => 'Rajasthan',
                    'pincode' => '302001',
                    'gst_number' => '08AAAAA' . rand(1000, 9999) . 'A1Z' . rand(0, 9),
                    'pan_number' => 'ABCDE' . rand(1000, 9999) . 'F',
                    'bank_name' => 'State Bank of India',
                    'bank_account_name' => $storeName,
                    'bank_account_number' => '30010045' . rand(1000, 9999),
                    'bank_ifsc_code' => 'SBIN0000101',
                    'commission_rate' => 10.00,
                    'status' => 'approved'
                ]);

                $vendors[] = $vendor;
            }

            // 4. Seed 6 Categories
            $categoriesData = [
                'Necklaces' => '📿',
                'Rings' => '💍',
                'Earrings' => '💎',
                'Bangles & Bracelets' => '✨',
                'Chains' => '🔗',
                'Coins & Bars' => '🪙'
            ];

            $categories = [];
            $i = 1;
            foreach ($categoriesData as $catName => $icon) {
                $categories[] = Category::firstOrCreate(['slug' => Str::slug($catName)], [
                    'name' => $catName,
                    'description' => 'Beautiful ' . $catName . ' collection.',
                    'icon' => $icon,
                    'sort_order' => $i++,
                    'status' => 'active'
                ]);
            }

            // 5. Seed 20-30 Products & Variants
            $productMockData = [
                ['name' => 'Royal Kundan Gold Necklace', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Necklaces', 'weight' => 28.50, 'making' => 450],
                ['name' => 'Sleek 18K Diamond Ring', 'metal' => 'gold', 'purity' => '18K', 'category' => 'Rings', 'weight' => 4.20, 'making' => 600],
                ['name' => 'Art Deco Gold Jhumkas', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Earrings', 'weight' => 12.80, 'making' => 500],
                ['name' => 'Sterling Silver Kada', 'metal' => 'silver', 'purity' => '925', 'category' => 'Bangles & Bracelets', 'weight' => 24.00, 'making' => 80],
                ['name' => 'Classic Gold Curb Chain', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Chains', 'weight' => 15.00, 'making' => 300],
                ['name' => 'Fine Silver Lakshmi Coin', 'metal' => 'silver', 'purity' => '999', 'category' => 'Coins & Bars', 'weight' => 50.00, 'making' => 30],
                ['name' => 'Elegant Solitaire Ring', 'metal' => 'gold', 'purity' => '18K', 'category' => 'Rings', 'weight' => 3.50, 'making' => 700],
                ['name' => 'Artisan Filigree Silver Earrings', 'metal' => 'silver', 'purity' => '925', 'category' => 'Earrings', 'weight' => 8.20, 'making' => 95],
                ['name' => 'Gold Bis Hallmarked Coin 10g', 'metal' => 'gold', 'purity' => '24K', 'category' => 'Coins & Bars', 'weight' => 10.00, 'making' => 150],
                ['name' => 'Bridal Choker Necklace Set', 'metal' => 'gold', 'purity' => '22K', 'category' => 'Necklaces', 'weight' => 45.00, 'making' => 550],
            ];

            // Loop and expand to 20 products
            $products = [];
            for ($k = 0; $k < 20; $k++) {
                $mock = $productMockData[$k % count($productMockData)];
                $vendor = $vendors[$k % count($vendors)];
                $category = collect($categories)->first(fn($c) => $c->name === $mock['category']);

                $product = Product::firstOrCreate(['slug' => Str::slug($mock['name'] . '-' . $vendor->id)], [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'name' => $mock['name'],
                    'metal_type' => $mock['metal'],
                    'purity' => $mock['purity'],
                    'weight_grams' => $mock['weight'],
                    'making_charge_per_gram' => $mock['making'],
                    'description' => 'Beautifully designed premium ' . $mock['name'] . ' crafted by ' . $vendor->store_name . '.',
                    'status' => 'active',
                    'is_featured' => ($k % 3 === 0)
                ]);

                // Create variants for Rings or Chains
                if ($mock['category'] === 'Rings') {
                    foreach ([12, 14, 16] as $size) {
                        ProductVariant::firstOrCreate([
                            'product_id' => $product->id,
                            'name' => 'Size ' . $size,
                        ], [
                            'sku' => strtoupper(substr($mock['metal'], 0, 1) . '-' . $product->id . '-' . $size),
                            'weight_grams' => $mock['weight'] + ($size - 14) * 0.2,
                            'inventory_qty' => 5,
                        ]);
                    }
                } else {
                    ProductVariant::firstOrCreate([
                        'product_id' => $product->id,
                        'name' => 'Standard',
                    ], [
                        'sku' => strtoupper(substr($mock['metal'], 0, 1) . '-' . $product->id . '-STD'),
                        'weight_grams' => $mock['weight'],
                        'inventory_qty' => 10,
                    ]);
                }

                // Add placeholder product images
                ProductImage::firstOrCreate([
                    'product_id' => $product->id,
                    'is_primary' => true,
                ], [
                    'file_path' => 'products/placeholder_' . $mock['metal'] . '.png',
                    'sort_order' => 1,
                ]);

                $products[] = $product;
            }

            // 6. Seed Mock Transactions (Orders, Payouts, Reviews)
            // Order 1: Completed Order by Customer 1
            $orderProduct = $products[0];
            $variant = $orderProduct->variants()->first();
            $weight = $variant->weight_grams;
            
            $rateObj = MetalRate::getLatestRate($orderProduct->metal_type, $orderProduct->purity);
            $metalPrice = $weight * $rateObj->rate_per_gram;
            $makingPrice = $weight * $orderProduct->making_charge_per_gram;
            $subtotal = $metalPrice + $makingPrice;
            $gst = $subtotal * 0.03;
            $total = $subtotal + $gst;

            $order = Order::firstOrCreate(['order_number' => 'ORD-' . today()->format('Ymd') . '-001'], [
                'user_id' => $customer1->id,
                'customer_name' => $customer1->name,
                'customer_email' => $customer1->email,
                'customer_phone' => $customer1->phone,
                'shipping_address' => 'Flat 402, Royal Residency, MG Road, Mumbai, Maharashtra - 400001',
                'billing_address' => 'Flat 402, Royal Residency, MG Road, Mumbai, Maharashtra - 400001',
                'subtotal' => $subtotal,
                'cgst_amount' => $gst / 2,
                'sgst_amount' => $gst / 2,
                'igst_amount' => 0.00,
                'total_amount' => $total,
                'payment_status' => 'paid',
                'payment_method' => 'razorpay',
                'payment_id' => 'pay_mock1234567',
                'status' => 'completed',
            ]);

            OrderItem::firstOrCreate([
                'order_id' => $order->id,
                'product_id' => $orderProduct->id,
            ], [
                'product_name' => $orderProduct->name,
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'sku' => $variant->sku,
                'metal_type' => $orderProduct->metal_type,
                'purity' => $orderProduct->purity,
                'weight_grams' => $weight,
                'rate_per_gram' => $rateObj->rate_per_gram,
                'making_charge_per_gram' => $orderProduct->making_charge_per_gram,
                'unit_price' => $subtotal,
                'quantity' => 1,
                'subtotal' => $subtotal,
                'cgst_amount' => $gst / 2,
                'sgst_amount' => $gst / 2,
                'igst_amount' => 0.00,
                'total_amount' => $total,
            ]);

            // Seed Commission & Payout for Order 1
            $commRate = $orderProduct->vendor->commission_rate;
            $commAmount = $subtotal * ($commRate / 100);
            $vendorEarnings = $subtotal - $commAmount;

            Commission::firstOrCreate([
                'order_id' => $order->id,
                'vendor_id' => $orderProduct->vendor_id,
            ], [
                'order_item_id' => 1, // Assume first ID
                'subtotal' => $subtotal,
                'commission_rate' => $commRate,
                'commission_amount' => $commAmount,
                'vendor_earnings' => $vendorEarnings,
                'status' => 'earned'
            ]);

            Payout::firstOrCreate([
                'vendor_id' => $orderProduct->vendor_id,
                'payout_number' => 'PAY-' . today()->format('Ymd') . '-001',
            ], [
                'amount' => $vendorEarnings,
                'status' => 'pending',
                'bank_details' => 'SBI A/C: ' . $orderProduct->vendor->bank_account_number,
            ]);

            // Seed Product Reviews
            Review::firstOrCreate([
                'product_id' => $orderProduct->id,
                'user_id' => $customer1->id,
            ], [
                'rating' => 5,
                'comment' => 'Absolutely gorgeous piece! The gold quality and hand craftsmanship is exceptional. Strongly recommend this seller.',
                'status' => 'approved',
            ]);
        });
    }
}
```

---

### `app/Providers/AppServiceProvider.php` — Add `Gate::before`

Add the following inside the `boot()` method of `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Grant super_admin all permissions without explicit assignment.
        // This must be registered BEFORE any policy checks.
        Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
```

> **Important:** The `Gate::before` callback returns `true` for `super_admin`, which short-circuits all policy and gate checks. Returning `null` (no return) for other users ensures their normal permissions are checked.

---

## Artisan Commands

```bash
# Run only the roles/permissions seeder (safe to re-run — uses firstOrCreate + syncPermissions)
php artisan db:seed --class=RolesAndPermissionsSeeder

# Or run all seeders
php artisan db:seed

# Create storage symlink
php artisan storage:link

# Generate placeholder images to avoid 403 / 404 image errors
php -r "if(!is_dir('storage/app/public/products')) { mkdir('storage/app/public/products', 0755, true); } $g = imagecreatetruecolor(400, 400); imagefill($g, 0, 0, imagecolorallocate($g, 201, 168, 76)); imagepng($g, 'storage/app/public/products/placeholder_gold.png'); imagedestroy($g); $s = imagecreatetruecolor(400, 400); imagefill($s, 0, 0, imagecolorallocate($s, 192, 192, 192)); imagepng($s, 'storage/app/public/products/placeholder_silver.png'); imagedestroy($s);"

# Verify permissions were created
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count() . ' permissions created';"

# Verify roles
php artisan tinker --execute="\Spatie\Permission\Models\Role::all(['name','guard_name'])->each(fn(\$r)=>print(\$r->name.PHP_EOL));"

# Assign super_admin role to a user (replace 1 with your admin user ID)
php artisan tinker --execute="\App\Models\User::find(1)->assignRole('super_admin');"
```

---

## Notes

- **Idempotency**: Using `firstOrCreate` + `syncPermissions` means this seeder is safe to re-run without creating duplicates.
- **FilamentShield integration**: FilamentShield generates its own Filament-specific permissions (e.g., `view_any_vendor`) in addition to these domain permissions. Run `php artisan shield:generate --all` after registering Resources to auto-generate those.
- **Guard**: All permissions use the `web` guard. Filament 5 uses the same `web` guard by default.
- **Cache**: Always call `app()[PermissionRegistrar::class]->forgetCachedPermissions()` at the start of the seeder to avoid stale cache issues.
- **Vendor/Customer panel access**: Roles `vendor`, `vendor_staff`, and `customer` have no Filament panel access by default. Panel access is controlled by the `canAccessPanel` method on the `User` model.
