<?php

namespace App\Filament\Vendor\Widgets;

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

        $pendingPayout = OrderItem::where('vendor_id', $vendorId)
            ->where('fulfillment_status', '!=', 'cancelled')
            ->sum('total_price');

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
