<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Vendor;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (empty($data['vendor_id'])) {
            $vendor = Vendor::first();
            if (!$vendor) {
                $vendor = Vendor::create([
                    'user_id'    => auth()->id(),
                    'store_name' => 'Pinora Store',
                    'store_slug' => 'pinora-store',
                    'status'     => 'approved',
                ]);
            }
            $data['vendor_id'] = $vendor->id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
