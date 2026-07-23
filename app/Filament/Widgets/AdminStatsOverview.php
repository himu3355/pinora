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
    protected ?string $pollingInterval = '30s';

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
        $activeProducts = Product::where('status', 'active')->count();

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
        $pendingPayoutsAmount = Payout::where('status', 'draft')
            ->orWhere('status', 'processed')
            ->sum('final_payout_amount');

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
