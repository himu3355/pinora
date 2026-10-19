<?php

namespace App\Filament\Resources\MetalRates\Pages;

use App\Filament\Resources\MetalRates\MetalRateResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMetalRate extends CreateRecord
{
    protected static string $resource = MetalRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enter_all_rates')
                ->label("Enter All Rates at Once")
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->url(fn (): string => \App\Filament\Pages\TodayMetalRates::getUrl()),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
