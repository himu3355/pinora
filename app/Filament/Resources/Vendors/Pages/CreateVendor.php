<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $vendor = $this->record;

        if (! $vendor->subscription) {
            \App\Models\VendorSubscription::create([
                'vendor_id'     => $vendor->id,
                'status'        => 'trialing',
                'trial_ends_at' => now()->addDays(14),
            ]);
        }
    }
}
