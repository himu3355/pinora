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
                ->label(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'Re-sync GoldAPI' : 'Sync Daily Rates')
                ->icon('heroicon-o-arrow-path')
                ->color(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'warning' : 'success')
                ->modalHeading(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'Today\'s Rates Already Synced' : 'Sync Live Rates from GoldAPI')
                ->modalDescription(function (MetalRateSyncService $sync) {
                    if ($sync->hasSyncedToday()) {
                        $time = $sync->getLastSyncedAt() ? \Carbon\Carbon::parse($sync->getLastSyncedAt())->format('h:i A') : 'earlier today';
                        return "⚠️ Rates have already been synced today at {$time}. GoldAPI free tier is strictly limited to 100 requests/month (3 requests per sync = 90 requests/month). Re-syncing today will consume an extra 3 requests from your quota.";
                    }
                    return 'Fetches today\'s live market bullion rates and auto-calculates alloy rates (uses 3 requests).';
                })
                ->modalSubmitActionLabel(fn (MetalRateSyncService $sync) => $sync->hasSyncedToday() ? 'Confirm & Force Sync' : 'Sync Now')
                ->form(function (MetalRateSyncService $sync) {
                    $fields = [
                        \Filament\Forms\Components\TextInput::make('token')
                            ->label('GoldAPI Access Token')
                            ->default(fn () => \App\Models\Setting::get('goldapi_key') ?: config('services.goldapi.key') ?: env('GOLDAPI_KEY'))
                            ->password()
                            ->revealable()
                            ->placeholder('goldapi-xxxxxxxxxxxx-io')
                            ->helperText('Free token from https://www.goldapi.io. Saved automatically once entered.'),
                    ];

                    if ($sync->hasSyncedToday()) {
                        $fields[] = \Filament\Forms\Components\Toggle::make('force')
                            ->label('I understand: Force duplicate re-sync today (uses 3 extra requests)')
                            ->default(false)
                            ->required()
                            ->helperText('Enable this toggle to confirm you want to proceed with a second sync today.');
                    }

                    return $fields;
                })
                ->action(function (array $data, MetalRateSyncService $syncService) {
                    if ($syncService->hasSyncedToday() && empty($data['force'])) {
                        Notification::make()
                            ->title('Sync Cancelled')
                            ->body('Duplicate sync skipped to protect your 100 requests/month quota.')
                            ->info()
                            ->send();
                        return;
                    }

                    if (!empty($data['token'])) {
                        \App\Models\Setting::set('goldapi_key', trim($data['token']));
                    }

                    $force = (bool) ($data['force'] ?? false);

                    if ($syncService->sync($data['token'] ?? null, force: $force)) {
                        Notification::make()
                            ->title('Rates Synced Successfully')
                            ->body('Market rates fetched from GoldAPI and updated.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sync Blocked / Failed')
                            ->body($syncService->getLastError() ?: 'Could not sync rates from GoldAPI.')
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
