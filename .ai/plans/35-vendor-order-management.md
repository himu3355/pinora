# Step 35: Vendor Panel — Order Management

**Goal:** Create a Filament Resource for vendors to view order items that contain their products, view full customer and shipping details, see the GST breakdown, and update the fulfillment status and tracking information.
**Depends On:** Step 33 (Vendor Panel Setup), Step 20 (Order & OrderItem Models)
**Next Step:** Step 36 (Vendor Customer View)

---

## Goal Explanation

In a multi-vendor platform, a single customer order may contain products from different vendors. Consequently, vendors should not manage the global `Order` record directly; instead, they manage their specific `OrderItem` records.

This step implements the `OrderItemResource` inside the Vendor panel. The vendor can:
1. View a list of order items containing their products.
2. View full customer shipping details and financial/GST breakdowns.
3. Update the fulfillment status (`pending`, `accepted`, `processing`, `shipped`, `delivered`, `cancelled`).
4. Enter courier and tracking info (`courier_name`, `tracking_number`, `tracking_url`).
5. **Restriction:** The vendor cannot modify the price, quantity, or other financial fields, nor can they cancel the overall customer order itself.

---

## Files to Create

1. `app/Filament/Vendor/Resources/OrderItemResource.php`
2. `app/Filament/Vendor/Resources/OrderItemResource/Pages/ListOrderItems.php`
3. `app/Filament/Vendor/Resources/OrderItemResource/Pages/ViewOrderItem.php`

---

## Complete PHP Code

### 1. `app/Filament/Vendor/Resources/OrderItemResource.php`

```php
<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'My Orders';

    protected static ?string $pluralLabel = 'My Orders';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['order.user'])
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Form $form): Form
    {
        // Vendor panel order management is read-only for forms.
        // Status and tracking updates are handled via custom actions.
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('variant_name')
                    ->label('Variant')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total (Inc. Tax)')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fulfillment_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'accepted' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.shipping_name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('order.shipping_phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('fulfillment_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'To ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('fulfillment_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'accepted' => 'Accepted',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default(fn (OrderItem $record): string => $record->fulfillment_status),
                    ])
                    ->action(function (OrderItem $record, array $data): void {
                        $updateData = ['fulfillment_status' => $data['fulfillment_status']];

                        if ($data['fulfillment_status'] === 'shipped') {
                            $updateData['shipped_at'] = now();
                        } elseif ($data['fulfillment_status'] === 'delivered') {
                            $updateData['delivered_at'] = now();
                        }

                        $record->update($updateData);
                    }),

                Action::make('addTracking')
                    ->label('Add Tracking')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('courier_name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (OrderItem $record): ?string => $record->courier_name),
                        Forms\Components\TextInput::make('tracking_number')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (OrderItem $record): ?string => $record->tracking_number),
                        Forms\Components\TextInput::make('tracking_url')
                            ->url()
                            ->maxLength(500)
                            ->default(fn (OrderItem $record): ?string => $record->tracking_url),
                    ])
                    ->action(function (OrderItem $record, array $data): void {
                        $record->update([
                            'courier_name' => $data['courier_name'],
                            'tracking_number' => $data['tracking_number'],
                            'tracking_url' => $data['tracking_url'],
                            'fulfillment_status' => 'shipped',
                            'shipped_at' => $record->shipped_at ?? now(),
                        ]);
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Order & Status')
                            ->columnSpan(2)
                            ->schema([
                                Infolists\Components\Grid::make(3)->schema([
                                    Infolists\Components\TextEntry::make('order.order_number')
                                        ->label('Order Number')
                                        ->weight('bold'),
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Placed At')
                                        ->dateTime('d M Y h:i A'),
                                    Infolists\Components\TextEntry::make('fulfillment_status')
                                        ->label('Fulfillment Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'pending' => 'gray',
                                            'accepted' => 'info',
                                            'processing' => 'warning',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'gray',
                                        }),
                                ]),
                            ]),

                        Infolists\Components\Section::make('Customer Info')
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('order.shipping_name')
                                    ->label('Customer Name'),
                                Infolists\Components\TextEntry::make('order.shipping_phone')
                                    ->label('Customer Phone'),
                                Infolists\Components\TextEntry::make('order.customer_email')
                                    ->label('Customer Email')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Product Details')
                            ->columnSpan(2)
                            ->schema([
                                Infolists\Components\Grid::make(3)->schema([
                                    Infolists\Components\TextEntry::make('product_name')
                                        ->label('Product Name')
                                        ->weight('bold'),
                                    Infolists\Components\TextEntry::make('variant_name')
                                        ->label('Variant')
                                        ->placeholder('—'),
                                    Infolists\Components\TextEntry::make('sku')
                                        ->label('SKU')
                                        ->placeholder('—'),
                                    Infolists\Components\TextEntry::make('quantity')
                                        ->label('Quantity'),
                                    Infolists\Components\TextEntry::make('metal_type')
                                        ->label('Metal Type')
                                        ->badge(),
                                    Infolists\Components\TextEntry::make('purity')
                                        ->label('Purity'),
                                    Infolists\Components\TextEntry::make('weight_grams')
                                        ->label('Weight (grams)')
                                        ->suffix(' g'),
                                    Infolists\Components\TextEntry::make('metal_rate_used')
                                        ->label('Metal Rate')
                                        ->money('INR'),
                                    Infolists\Components\TextEntry::make('making_charges')
                                        ->label('Making Charges')
                                        ->money('INR'),
                                ]),
                            ]),

                        Infolists\Components\Section::make('Shipping Address')
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('order.shipping_address_line_1')
                                    ->label('Address'),
                                Infolists\Components\TextEntry::make('order.shipping_address_line_2')
                                    ->label('Address Line 2')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('order.shipping_city')
                                    ->label('City'),
                                Infolists\Components\TextEntry::make('order.shipping_state')
                                    ->label('State'),
                                Infolists\Components\TextEntry::make('order.shipping_pincode')
                                    ->label('Pincode'),
                                Infolists\Components\TextEntry::make('order.shipping_country')
                                    ->label('Country'),
                            ]),
                    ]),

                Infolists\Components\Grid::make(3)
                    ->schema([
                        Infolists\Components\Section::make('Financial Summary (GST Breakdown)')
                            ->columnSpan(2)
                            ->schema([
                                Infolists\Components\Grid::make(4)->schema([
                                    Infolists\Components\TextEntry::make('unit_price')
                                        ->label('Unit Price (Pre-GST)')
                                        ->money('INR'),
                                    Infolists\Components\TextEntry::make('subtotal')
                                        ->label('Subtotal')
                                        ->money('INR'),
                                    Infolists\Components\TextEntry::make('cgst_amount')
                                        ->label('CGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->cgst_amount} ({$record->cgst_rate}%)"),
                                    Infolists\Components\TextEntry::make('sgst_amount')
                                        ->label('SGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->sgst_amount} ({$record->sgst_rate}%)"),
                                    Infolists\Components\TextEntry::make('igst_amount')
                                        ->label('IGST')
                                        ->state(fn (OrderItem $record): string => "₹{$record->igst_amount} ({$record->igst_rate}%)"),
                                    Infolists\Components\TextEntry::make('total_price')
                                        ->label('Final Total')
                                        ->money('INR')
                                        ->weight('bold'),
                                ]),
                            ]),

                        Infolists\Components\Section::make('Tracking Details')
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('courier_name')
                                    ->label('Courier')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('tracking_number')
                                    ->label('Tracking No.')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('tracking_url')
                                    ->label('Tracking URL')
                                    ->url()
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('shipped_at')
                                    ->label('Shipped At')
                                    ->dateTime('d M Y h:i A')
                                    ->placeholder('—'),
                                Infolists\Components\TextEntry::make('delivered_at')
                                    ->label('Delivered At')
                                    ->dateTime('d M Y h:i A')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderItems::route('/'),
            'view' => Pages\ViewOrderItem::route('/{record}'),
        ];
    }
}
```

---

### 2. `app/Filament/Vendor/Resources/OrderItemResource/Pages/ListOrderItems.php`

```php
<?php

namespace App\Filament\Vendor\Resources\OrderItemResource\Pages;

use App\Filament\Vendor\Resources\OrderItemResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderItems extends ListRecords
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

---

### 3. `app/Filament/Vendor/Resources/OrderItemResource/Pages/ViewOrderItem.php`

```php
<?php

namespace App\Filament\Vendor\Resources\OrderItemResource\Pages;

use App\Filament\Vendor\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderItem extends ViewRecord
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('fulfillment_status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'accepted' => 'Accepted',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default(fn (): string => $this->record->fulfillment_status),
                ])
                ->action(function (array $data): void {
                    $updateData = ['fulfillment_status' => $data['fulfillment_status']];

                    if ($data['fulfillment_status'] === 'shipped') {
                        $updateData['shipped_at'] = now();
                    } elseif ($data['fulfillment_status'] === 'delivered') {
                        $updateData['delivered_at'] = now();
                    }

                    $this->record->update($updateData);
                }),

            Actions\Action::make('addTracking')
                ->label('Add Tracking')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\TextInput::make('courier_name')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): ?string => $this->record->courier_name),
                    \Filament\Forms\Components\TextInput::make('tracking_number')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): ?string => $this->record->tracking_number),
                    \Filament\Forms\Components\TextInput::make('tracking_url')
                        ->url()
                        ->maxLength(500)
                        ->default(fn (): ?string => $this->record->tracking_url),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'courier_name' => $data['courier_name'],
                        'tracking_number' => $data['tracking_number'],
                        'tracking_url' => $data['tracking_url'],
                        'fulfillment_status' => 'shipped',
                        'shipped_at' => $this->record->shipped_at ?? now(),
                    ]);
                }),
        ];
    }
}
```

---

## Notes

- **Query Isolation:** `getEloquentQuery()` guarantees that vendors can only access order items that contain their own products (`vendor_id` constraint). Attempting to guess an ID in the URL for another vendor's item results in a 404.
- **Financial Controls:** Vendors have no form inputs for price, quantity, or GST fields. They are fully read-only to prevent manipulation.
- **Actions Sync:** The actions `updateStatus` and `addTracking` are registered both in the table row and the View page for convenience.
- **GST Display:** Displays CGST, SGST, or IGST dynamically based on the stored rates.
