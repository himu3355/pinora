<?php

namespace App\Filament\Vendor\Resources\OrderItemResource\Pages;

use App\Filament\Vendor\Resources\OrderItemResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderItems extends ListRecords
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
