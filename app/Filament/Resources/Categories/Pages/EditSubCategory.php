<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\SubCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubCategory extends EditRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
