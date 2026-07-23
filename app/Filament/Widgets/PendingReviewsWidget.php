<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingReviewsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '60s';

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
                ->url(\App\Filament\Resources\Reviews\ReviewResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'pending']]])),

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
