<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Orders';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->weight('semibold')
                    ->searchable()
                    ->url(fn(Order $record) => \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $record])),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'gray',
                        'confirmed'  => 'info',
                        'processing' => 'warning',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid'    => 'success',
                        'failed'  => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated(false);
    }
}
