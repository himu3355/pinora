<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static \UnitEnum|string|null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Only show users who have the 'customer' role OR have at least one order.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $q) {
                $q->whereHas('roles', fn(Builder $r) => $r->where('name', 'customer'))
                  ->orWhereHas('orders');
            });
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view'   => ViewCustomer::route('/{record}'),
            'edit'   => EditCustomer::route('/{record}/edit'),
        ];
    }
}
