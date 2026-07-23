<?php

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Vendor Dashboard';
    protected static ?int    $navigationSort  = 0;
}
