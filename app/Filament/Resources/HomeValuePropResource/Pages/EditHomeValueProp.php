<?php

namespace App\Filament\Resources\HomeValuePropResource\Pages;

use App\Filament\Resources\HomeValuePropResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeValueProp extends EditRecord
{
    protected static string $resource = HomeValuePropResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
