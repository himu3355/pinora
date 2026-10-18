<?php

namespace App\Filament\Resources\HomeValuePropResource\Pages;

use App\Filament\Resources\HomeValuePropResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeValueProps extends ListRecords
{
    protected static string $resource = HomeValuePropResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
