<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\SubCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubCategories extends ListRecords
{
    protected static string $resource = SubCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
