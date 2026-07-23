<?php

namespace App\Filament\Vendor\Resources\VendorCustomerResource\Pages;

use App\Filament\Vendor\Resources\VendorCustomerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorCustomer extends ViewRecord
{
    protected static string $resource = VendorCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
