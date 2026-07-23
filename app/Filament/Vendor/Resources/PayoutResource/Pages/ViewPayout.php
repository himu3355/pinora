<?php

namespace App\Filament\Vendor\Resources\PayoutResource\Pages;

use App\Filament\Vendor\Resources\PayoutResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPayout extends ViewRecord
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
