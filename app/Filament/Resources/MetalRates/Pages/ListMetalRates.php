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
            CreateAction::make()->label('Add Today\'s Rate'),
            Action::make('sync')
                ->label('Sync Daily Rates')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function (MetalRateSyncService $syncService) {
                    if ($syncService->sync()) {
                        Notification::make()
                            ->title('Rates Synced Successfully')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Could not sync rates. Using last successfully stored rates.')
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
