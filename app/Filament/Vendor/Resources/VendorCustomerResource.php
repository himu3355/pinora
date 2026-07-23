<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\VendorCustomerResource\Pages\ListVendorCustomers;
use App\Filament\Vendor\Resources\VendorCustomerResource\Pages\ViewVendorCustomer;
use App\Filament\Vendor\Resources\VendorCustomerResource\RelationManagers\OrderItemsRelationManager;
use App\Models\User;
use App\Services\VendorContext;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Schemas\Components\Grid as InfoGrid;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class VendorCustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $pluralLabel = 'Customers';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $vendor = auth()->user()->vendor;
        return $vendor && $vendor->hasActiveSubscription();
    }

    public static function getEloquentQuery(): Builder
    {
        $vendorId = VendorContext::currentId();

        // Scope to users who have ordered this vendor's products,
        // and pre-calculate vendor-specific aggregates.
        return parent::getEloquentQuery()
            ->select('users.*')
            ->selectSub(function ($query) use ($vendorId) {
                $query->selectRaw('COALESCE(SUM(order_items.total_price), 0)')
                    ->from('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('order_items.vendor_id', $vendorId);
            }, 'vendor_total_spent')
            ->selectSub(function ($query) use ($vendorId) {
                $query->selectRaw('COUNT(DISTINCT orders.id)')
                    ->from('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('order_items.vendor_id', $vendorId);
            }, 'vendor_orders_count')
            ->selectSub(function ($query) use ($vendorId) {
                $query->selectRaw('MAX(orders.created_at)')
                    ->from('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('order_items.vendor_id', $vendorId);
            }, 'vendor_last_order_date')
            ->whereHas('orders.items', fn ($q) => $q->where('vendor_id', $vendorId));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]); // Read-only resource, no form needed
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->disk('public'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('vendor_orders_count')
                    ->label('My Orders')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_orders_count', $direction))
                    ->alignCenter(),

                TextColumn::make('vendor_total_spent')
                    ->label('Total Sales')
                    ->money('INR')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_total_spent', $direction)),

                TextColumn::make('vendor_last_order_date')
                    ->label('Last Order')
                    ->dateTime('d M Y')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_last_order_date', $direction))
                    ->placeholder('—'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                InfoSection::make('Customer Profile')
                    ->schema([
                        InfoGrid::make(3)->schema([
                            ImageEntry::make('avatar')
                                ->label('Avatar')
                                ->circular()
                                ->disk('public'),

                            TextEntry::make('name')
                                ->label('Full Name')
                                ->weight('bold'),

                            TextEntry::make('email')
                                ->label('Email Address')
                                ->copyable(),

                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->placeholder('—'),

                            TextEntry::make('gender')
                                ->label('Gender')
                                ->badge()
                                ->placeholder('—')
                                ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                            TextEntry::make('birthday')
                                ->label('Birthday')
                                ->date('d M Y')
                                ->placeholder('—'),

                            TextEntry::make('anniversary_date')
                                ->label('Anniversary')
                                ->date('d M Y')
                                ->placeholder('—'),

                            TextEntry::make('ring_size')
                                ->label('Ring Size')
                                ->placeholder('—'),

                            TextEntry::make('bangle_size')
                                ->label('Bangle Size')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorCustomers::route('/'),
            'view'  => ViewVendorCustomer::route('/{record}'),
        ];
    }
}
