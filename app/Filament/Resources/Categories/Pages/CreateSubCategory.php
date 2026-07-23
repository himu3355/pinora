<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\SubCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubCategory extends CreateRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
