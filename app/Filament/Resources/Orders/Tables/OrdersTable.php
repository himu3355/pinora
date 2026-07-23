<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Guest'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'gray',
                        'confirmed'  => 'info',
                        'processing' => 'warning',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        'refunded'   => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'confirmed'  => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                        'refunded'   => 'Refunded',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending'  => 'Pending',
                        'paid'     => 'Paid',
                        'failed'   => 'Failed',
                        'refunded' => 'Refunded',
                    ]),

                SelectFilter::make('vendor')
                    ->label('Vendor (has item)')
                    ->options(
                        Vendor::where('status', 'approved')
                            ->orderBy('store_name')
                            ->pluck('store_name', 'id')
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (!blank($data['value'])) {
                            $query->whereHas('items.product',
                                fn(Builder $q) => $q->where('vendor_id', $data['value'])
                            );
                        }
                        return $query;
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Placed From'),
                        DatePicker::make('until')->label('Placed Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'],
                                fn(Builder $q, $d) => $q->whereDate('created_at', '>=', $d)
                            )
                            ->when($data['until'],
                                fn(Builder $q, $d) => $q->whereDate('created_at', '<=', $d)
                            );
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),

                Action::make('cancel')
                    ->label('Cancel Order')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription('Are you sure? This action cannot be undone. Stock will be restored.')
                    ->visible(fn(Order $record) => !in_array($record->status, ['cancelled', 'delivered', 'refunded']))
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'cancelled']);
                        // Restore stock for each item
                        foreach ($record->items as $item) {
                            if ($item->product) {
                                $item->product->increment('stock_quantity', $item->quantity);
                            }
                        }
                        Notification::make()
                            ->title('Order cancelled and stock restored')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('No orders found')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
