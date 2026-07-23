<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $vendorId = VendorContext::currentId();

        return $table
            ->query(
                OrderItem::query()
                    ->selectRaw('product_id as id, product_id, product_name, SUM(quantity) as total_qty, SUM(total_price) as total_sales')
                    ->where('vendor_id', $vendorId)
                    ->where('fulfillment_status', 'delivered')
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product Name')
                    ->weight('bold'),

                TextColumn::make('total_qty')
                    ->label('Units Sold')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total_sales')
                    ->label('Total Revenue')
                    ->money('INR')
                    ->sortable(),
            ])
            ->paginated(false)
            ->actions([
                Action::make('view')
                    ->label('Manage Product')
                    ->icon('heroicon-o-cube')
                    ->url(fn ($record): string => \App\Filament\Vendor\Resources\ProductResource::getUrl('edit', ['record' => $record->product_id ?? 0]))
                    ->visible(fn ($record): bool => !is_null($record->product_id)),
            ]);
    }
}
