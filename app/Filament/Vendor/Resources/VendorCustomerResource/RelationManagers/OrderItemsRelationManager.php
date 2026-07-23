<?php

namespace App\Filament\Vendor\Resources\VendorCustomerResource\RelationManagers;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orders'; // Linked through users -> orders

    protected static ?string $title = 'Purchased Items';

    public function table(Table $table): Table
    {
        $vendorId = VendorContext::currentId();

        // Scope the table query so that the vendor ONLY sees
        // orders containing their items.
        return $table
            ->recordTitleAttribute('order_number')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereHas('items', fn ($q) => $q->where('vendor_id', $vendorId))
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M Y h:i A'),

                TextColumn::make('items')
                    ->label('Products Ordered')
                    ->state(function ($record) use ($vendorId) {
                        return $record->items()
                            ->where('vendor_id', $vendorId)
                            ->get()
                            ->map(fn (OrderItem $item) => "{$item->product_name} (x{$item->quantity})")
                            ->join(', ');
                    })
                    ->wrap(),

                TextColumn::make('vendor_earnings')
                    ->label('My Earnings')
                    ->money('INR')
                    ->state(function ($record) use ($vendorId) {
                        return $record->items()
                            ->where('vendor_id', $vendorId)
                            ->sum('total_price');
                    }),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'gray',
                        'confirmed'  => 'info',
                        'processing' => 'warning',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Action::make('view_order_item')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => \App\Filament\Vendor\Resources\OrderItemResource::getUrl('view', [
                        'record' => $record->items()->where('vendor_id', $vendorId)->first()?->id ?? 0
                    ])),
            ])
            ->bulkActions([]);
    }
}
