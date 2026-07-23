<?php

namespace App\Filament\Resources\MetalRates;

use App\Filament\Resources\MetalRates\Pages\CreateMetalRate;
use App\Filament\Resources\MetalRates\Pages\EditMetalRate;
use App\Filament\Resources\MetalRates\Pages\ListMetalRates;
use App\Filament\Resources\MetalRates\Schemas\MetalRateForm;
use App\Filament\Resources\MetalRates\Tables\MetalRatesTable;
use App\Models\MetalRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class MetalRateResource extends Resource
{
    protected static ?string $model = MetalRate::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCurrencyRupee;

    // protected static \UnitEnum|string|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Metal Rate';

    protected static ?string $pluralModelLabel = 'Metal Rates';

    public static function form(Schema $schema): Schema
    {
        return MetalRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetalRatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMetalRates::route('/'),
            'create' => CreateMetalRate::route('/create'),
            'edit'   => EditMetalRate::route('/{record}/edit'),
        ];
    }
}
