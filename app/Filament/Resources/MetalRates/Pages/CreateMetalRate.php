<?php

namespace App\Filament\Resources\MetalRates\Pages;

use App\Filament\Resources\MetalRates\MetalRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetalRate extends CreateRecord
{
    protected static string $resource = MetalRateResource::class;

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
