<?php

namespace App\Filament\Resources\MetalRates\Pages;

use App\Filament\Resources\MetalRates\MetalRateResource;
use App\Filament\Widgets\TodayMetalRatesWidget;
use App\Services\MetalRateSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMetalRates extends ListRecords
{
    protected static string $resource = MetalRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enter_today_rates')
                ->label("Enter Today's Rates (All Metals)")
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(fn (): string => \App\Filament\Pages\TodayMetalRates::getUrl()),
            CreateAction::make()
                ->label('Add Single Rate')
                ->outlined(),
            Action::make('sync')
                ->label(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'Re-sync Live API' : 'Sync Daily Rates')
                ->icon('heroicon-o-arrow-path')
                ->color(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'gray' : 'success')
                ->tooltip('Fetch today\'s live market bullion rates from Gold-API.com (Free & Unlimited)')
                ->action(function (MetalRateSyncService $syncService) {
                    if ($syncService->sync()) {
                        $body = $syncService->wasServedFromCache()
                            ? 'Rates refreshed using cached live market prices (anti-spam protection active to prevent IP blocking).'
                            : 'Live bullion market rates fetched from Gold-API.com and updated.';

                        Notification::make()
                            ->title('Rates Synced Successfully')
                            ->body($body)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sync Blocked / Failed')
                            ->body($syncService->getLastError() ?: 'Could not sync rates from Gold-API.com.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TodayMetalRatesWidget::class,
        ];
    }
}
