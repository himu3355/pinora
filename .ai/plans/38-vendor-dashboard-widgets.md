# Step 38: Vendor Panel Dashboard Widgets

**Goal:** Create performance and stats widgets for the vendor dashboard, including a metrics overview card, a recent orders tracker, and a top-performing products table.
**Depends On:** Step 33 (Vendor Panel Setup), Step 34 (Vendor Product Management), Step 35 (Vendor Order Management)
**Next Step:** Step 39 (Customer Registration & Guest Checkout)

---

## Goal Explanation

A dashboard needs to offer immediate, actionable insights when a vendor logs in. This step implements three custom widgets in `app/Filament/Vendor/Widgets/` that are auto-discovered by the vendor panel:

1. **`VendorStatsOverview`**:
   - Total Active Products.
   - Total Orders (items count).
   - Pending Orders (fulfillment_status = `pending`).
   - Total Revenue (sum of delivered items).
   - Pending Payout Balance (earnings not yet paid).
2. **`RecentOrderItemsWidget`**:
   - A tabular widget displaying the last 5 order items containing the vendor's products, showing status and order values.
3. **`TopProductsWidget`**:
   - A tabular widget showing the vendor's top 5 products ranked by quantity sold, including total units sold and total revenue.

---

## Files to Create

1. `app/Filament/Vendor/Widgets/VendorStatsOverview.php`
2. `app/Filament/Vendor/Widgets/RecentOrderItemsWidget.php`
3. `app/Filament/Vendor/Widgets/TopProductsWidget.php`

---

## Complete PHP Code

### 1. `app/Filament/Vendor/Widgets/VendorStatsOverview.php`

```php
<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Commission;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\VendorContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VendorStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $vendorId = VendorContext::currentId();

        $activeProductsCount = Product::where('vendor_id', $vendorId)
            ->where('status', 'active')
            ->count();

        $totalOrdersCount = OrderItem::where('vendor_id', $vendorId)
            ->count();

        $pendingOrdersCount = OrderItem::where('vendor_id', $vendorId)
            ->where('fulfillment_status', 'pending')
            ->count();

        $totalRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('fulfillment_status', 'delivered')
            ->sum('total_price');

        $pendingPayout = Commission::where('vendor_id', $vendorId)
            ->where('status', 'pending')
            ->sum('vendor_earnings');

        return [
            Stat::make('Active Products', $activeProductsCount)
                ->description('Products visible in storefront')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Pending Orders', $pendingOrdersCount)
                ->description('Awaiting fulfillment action')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($pendingOrdersCount > 0 ? 'warning' : 'gray'),

            Stat::make('Total Orders', $totalOrdersCount)
                ->description('All customer orders placed')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Delivered Revenue', '₹' . number_format($totalRevenue, 2))
                ->description('Settled earnings base')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Payouts', '₹' . number_format($pendingPayout, 2))
                ->description('Earnings awaiting payout')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
```

---

### 2. `app/Filament/Vendor/Widgets/RecentOrderItemsWidget.php`

```php
<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrderItemsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $vendorId = VendorContext::currentId();

        return $table
            ->query(
                OrderItem::query()
                    ->with(['order'])
                    ->where('vendor_id', $vendorId)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->limit(30),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Amount')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'accepted' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y h:i A'),
            ])
            ->paginated(false)
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (OrderItem $record): string => route('filament.vendor.resources.order-items.view', $record->id)),
            ]);
    }
}
```

---

### 3. `app/Filament/Vendor/Widgets/TopProductsWidget.php`

```php
<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $vendorId = VendorContext::currentId();

        return $table
            ->query(
                OrderItem::query()
                    ->selectRaw('product_id as id, product_id, product_name, SUM(quantity) as total_qty, SUM(total_price) as total_sales')
                    ->where('vendor_id', $vendorId)
                    ->where('fulfillment_status', 'delivered')
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Units Sold')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Revenue')
                    ->money('INR')
                    ->sortable(),
            ])
            ->paginated(false)
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label('Manage Product')
                    ->icon('heroicon-o-cube')
                    ->url(fn ($record): string => route('filament.vendor.resources.products.edit', $record->product_id ?? 0))
                    ->visible(fn ($record): bool => !is_null($record->product_id)),
            ]);
    }
}
```

---

## Notes

- **Widget Ordering:** The `sort` property dictates the display sequence: metrics are loaded first, followed by the recent orders list, and finally the top-selling products leaderboard.
- **Dynamic Context Scoping:** All database queries utilize the `VendorContext` service to automatically isolate vendor-specific data, preventing security breaches or data leaks.
- **Direct Navigation:** Action buttons are embedded directly inside row grids, allowing vendors to jump straight to product editing pages or order detail views in one click.
