<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class RecentOrderItemsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $vendorId = VendorContext::currentId();

        return $table
            ->query(
                OrderItem::query()
                    ->with(['order'])
                    ->where('vendor_id', $vendorId)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->weight('bold'),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->limit(30),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),

                TextColumn::make('total_price')
                    ->label('Amount')
                    ->money('INR'),

                TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'accepted' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y h:i A'),
            ])
            ->paginated(false)
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (OrderItem $record): string => \App\Filament\Vendor\Resources\OrderItemResource::getUrl('view', ['record' => $record->id])),
            ]);
    }
}
