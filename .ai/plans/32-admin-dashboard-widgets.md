# Step 32: Admin Panel — Dashboard Widgets

## Goal
Create four Filament dashboard widgets for the admin overview page, and register them in `AdminPanelProvider`. Widgets cover key platform metrics, recent orders, pending vendor applications, and pending reviews.

---

## Files to Create / Modify

| Action | File |
|--------|------|
| **Create** | `app/Filament/Widgets/AdminStatsOverview.php` |
| **Create** | `app/Filament/Widgets/RecentOrdersWidget.php` |
| **Create** | `app/Filament/Widgets/PendingVendorsWidget.php` |
| **Create** | `app/Filament/Widgets/PendingReviewsWidget.php` |
| **Modify** | `app/Providers/Filament/AdminPanelProvider.php` |

---

## PHP Code

### `app/Filament/Widgets/AdminStatsOverview.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\MetalRate;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today();

        // ── Vendors ──────────────────────────────────────────────────
        $approvedVendors = Vendor::where('status', 'approved')->count();
        $pendingVendors  = Vendor::where('status', 'pending')->count();

        // ── Customers ────────────────────────────────────────────────
        $totalCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->count();
        $newThisMonth   = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->count();

        // ── Products ─────────────────────────────────────────────────
        $activeProducts = Product::where('status', 'published')->count();

        // ── Orders (today) ───────────────────────────────────────────
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();

        // ── Revenue (today — paid orders only) ───────────────────────
        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $monthRevenue = Order::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // ── Reviews ──────────────────────────────────────────────────
        $pendingReviews = Review::where('status', 'pending')->count();

        // ── Payouts ──────────────────────────────────────────────────
        $pendingPayoutsAmount = Payout::where('status', 'pending')->sum('final_payout_amount');

        // ── Metal Rate (Gold 22K today) ───────────────────────────────
        $gold22k = MetalRate::whereDate('effective_date', $today)
            ->where('metal_type', 'gold')
            ->where('purity', '22K')
            ->latest()
            ->first();

        $goldRateDisplay = $gold22k
            ? '₹' . number_format($gold22k->rate_per_gram, 2) . ' / g'
            : 'Not set';

        return [
            Stat::make('Approved Vendors', $approvedVendors)
                ->description($pendingVendors . ' pending approval')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($pendingVendors > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Total Customers', number_format($totalCustomers))
                ->description('+' . $newThisMonth . ' this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Active Products', number_format($activeProducts))
                ->description('Published & available')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('success')
                ->icon('heroicon-o-squares-2x2'),

            Stat::make("Today's Orders", $todayOrders)
                ->description($pendingOrdersCount . ' pending')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($pendingOrdersCount > 5 ? 'warning' : 'gray')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make("Today's Revenue", '₹' . number_format($todayRevenue, 2))
                ->description('₹' . number_format($monthRevenue, 2) . ' this month')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('success')
                ->icon('heroicon-o-currency-rupee'),

            Stat::make('Pending Reviews', $pendingReviews)
                ->description($pendingReviews > 0 ? 'Requires moderation' : 'All reviewed')
                ->descriptionIcon($pendingReviews > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingReviews > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-star'),

            Stat::make('Pending Payouts', '₹' . number_format($pendingPayoutsAmount, 2))
                ->description('Unpaid vendor payouts')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingPayoutsAmount > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Gold 22K Rate', $goldRateDisplay)
                ->description($gold22k ? 'As of ' . today()->format('d M Y') : 'Enter today\'s rate')
                ->descriptionIcon($gold22k ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($gold22k ? 'warning' : 'danger')
                ->icon('heroicon-o-currency-rupee'),
        ];
    }
}
```

---

### `app/Filament/Widgets/RecentOrdersWidget.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->weight('semibold')
                    ->searchable()
                    ->url(fn(Order $record) => route('filament.admin.resources.orders.view', $record)),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'pending',
                        'info'    => 'confirmed',
                        'warning' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger'  => 'failed',
                    ]),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated(false);
    }
}
```

---

### `app/Filament/Widgets/PendingVendorsWidget.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingVendorsWidget extends TableWidget
{
    protected static ?string $heading = 'Vendors Awaiting Approval';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vendor::query()
                    ->where('status', 'pending')
                    ->with('user')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('store_name')
                    ->label('Store Name')
                    ->weight('semibold')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->color('gray'),

                TextColumn::make('city')
                    ->label('City')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->since(),
            ])
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Vendor $record) => VendorResource::getUrl('view', ['record' => $record])),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Vendor $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Vendor ' . $record->store_name . ' approved')
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),
            ])
            ->bulkActions([])
            ->paginated(false)
            ->emptyStateHeading('No pending vendors')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateDescription('All vendor applications have been reviewed.');
    }
}
```

---

### `app/Filament/Widgets/PendingReviewsWidget.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingReviewsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $pending  = Review::where('status', 'pending')->count();
        $approved = Review::where('status', 'approved')->count();
        $rejected = Review::where('status', 'rejected')->count();
        $total    = $pending + $approved + $rejected;

        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        return [
            Stat::make('Pending Reviews', $pending)
                ->description('Awaiting moderation')
                ->descriptionIcon($pending > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pending > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock')
                ->url(route('filament.admin.resources.reviews.index', ['tableFilters[status][value]' => 'pending'])),

            Stat::make('Approved Reviews', $approved)
                ->description('Live on storefront')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-star'),

            Stat::make('Approval Rate', $approvalRate . '%')
                ->description($rejected . ' rejected total')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($approvalRate >= 70 ? 'success' : 'warning')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
```

---

### `app/Providers/Filament/AdminPanelProvider.php` (modified)

```php
<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\PendingReviewsWidget;
use App\Filament\Widgets\PendingVendorsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('Pinora Admin')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AdminStatsOverview::class,
                RecentOrdersWidget::class,
                PendingVendorsWidget::class,
                PendingReviewsWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Vendors'),
                NavigationGroup::make('Catalog'),
                NavigationGroup::make('Orders'),
                NavigationGroup::make('Customers'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Finance'),
                NavigationGroup::make('Settings'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

---

## Artisan Commands

```bash
# Create all widgets (if building from scratch)
php artisan make:filament-widget AdminStatsOverview --stats-overview
php artisan make:filament-widget RecentOrdersWidget --table
php artisan make:filament-widget PendingVendorsWidget --table
php artisan make:filament-widget PendingReviewsWidget --stats-overview

# Clear Filament component cache after adding widgets
php artisan filament:cache-components

# Verify dashboard loads
php artisan route:list | grep admin
```

---

## Widget Registration Summary

| Widget | Type | Sort | Column Span | Polling |
|--------|------|------|------------|---------|
| `AdminStatsOverview` | `StatsOverviewWidget` | 1 | Full | 30s |
| `RecentOrdersWidget` | `TableWidget` | 2 | Full | None |
| `PendingVendorsWidget` | `TableWidget` | 3 | Full | None |
| `PendingReviewsWidget` | `StatsOverviewWidget` | 4 | Default | 60s |

---

## Notes

- **`$sort` property**: Controls the order widgets appear on the dashboard. Lower numbers appear first.
- **`$columnSpan = 'full'`**: Makes the table widgets span the full dashboard width for better readability.
- **`$pollingInterval`**: The stats widgets auto-refresh. Keep intervals reasonable to avoid excessive DB load — 30s for critical stats, 60s for less urgent counts.
- **`RecentOrdersWidget` URL**: Uses `route('filament.admin.resources.orders.view', $record)` — verify this route name matches the actual registered Filament route for your `OrderResource`.
- **`PendingReviewsWidget` URL**: The `->url()` method on a Stat links directly to the reviews list filtered by pending status. Adjust the query string format if needed.
- **`PendingVendorsWidget::resetTable()`**: Called after approve action to refresh the table and remove the approved vendor from the pending list without a full page reload.
- **Navigation groups in `AdminPanelProvider`**: The groups are declared in order — Filament will display navigation items grouped and sorted as declared. Add future navigation groups here as new features are added.
- **`discoverWidgets`**: Filament auto-discovers all widget classes in `app/Filament/Widgets/`. The explicit `->widgets([...])` array in the panel config ensures only the specified widgets appear on the dashboard. Widgets used only on resource list pages (like `TodayMetalRatesWidget`) should NOT be listed here — they are injected via `getHeaderWidgets()` on their respective pages.
- **Gold 22K rate**: The `AdminStatsOverview` queries today's rate from `MetalRate`. If no rate is entered, it shows a "Not set" warning stat in red to prompt the admin to enter it.
