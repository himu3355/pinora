<?php

namespace App\Filament\Vendor\Resources\ProductResource\Pages;

use App\Filament\Vendor\Resources\ProductResource;
use App\Services\VendorContext;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = VendorContext::currentId();
        return $data;
    }
}
