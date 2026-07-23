# Step 29: Admin Panel — Order Management Resource

## Goal
Create a Filament 5 Resource for viewing and managing all orders across all vendors. Includes a detailed view/edit page with order summary, customer info, shipping snapshot, read-only order items (with per-item fulfillment status), and financial breakdown.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/OrderResource.php` | Main resource class |
| `app/Filament/Resources/OrderResource/Pages/ListOrders.php` | List page |
| `app/Filament/Resources/OrderResource/Pages/ViewOrder.php` | View/Edit page |

---

## PHP Code

### `app/Filament/Resources/OrderResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Section 1: Order Summary ───────────────────────────────
            Section::make('Order Summary')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending'    => 'Pending',
                                'confirmed'  => 'Confirmed',
                                'processing' => 'Processing',
                                'shipped'    => 'Shipped',
                                'delivered'  => 'Delivered',
                                'cancelled'  => 'Cancelled',
                                'refunded'   => 'Refunded',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending'  => 'Pending',
                                'paid'     => 'Paid',
                                'failed'   => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'razorpay' => 'Razorpay',
                                'cod'      => 'Cash on Delivery',
                                'bank'     => 'Bank Transfer',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->maxLength(255),

                        DatePicker::make('paid_at')
                            ->label('Paid At'),
                    ]),

                    Textarea::make('notes')
                        ->label('Admin Notes')
                        ->rows(2)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),

            // ── Section 2: Customer ────────────────────────────────────
            Section::make('Customer')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('user.name')
                            ->label('Customer Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('user.email')
                            ->label('Customer Email')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('user.phone')
                            ->label('Customer Phone')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('guest_email')
                            ->label('Guest Email')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => blank($get('user_id'))),

                        TextInput::make('guest_phone')
                            ->label('Guest Phone')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => blank($get('user_id'))),
                    ]),
                ]),

            // ── Section 3: Shipping Address ────────────────────────────
            Section::make('Shipping Address (Snapshot)')
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('shipping_name')
                            ->label('Recipient Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_phone')
                            ->label('Phone')
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    TextInput::make('shipping_address_line1')
                        ->label('Address Line 1')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    TextInput::make('shipping_address_line2')
                        ->label('Address Line 2')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    Grid::make(4)->schema([
                        TextInput::make('shipping_city')
                            ->label('City')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_state')
                            ->label('State')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_pincode')
                            ->label('Pincode')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_country')
                            ->label('Country')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                ]),

            // ── Section 4: Order Items ─────────────────────────────────
            Section::make('Order Items')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Repeater::make('items')
                        ->relationship('items')
                        ->label('Items')
                        ->schema([
                            Grid::make(4)->schema([
                                TextInput::make('product_name')
                                    ->label('Product')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('variant_name')
                                    ->label('Variant')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('—'),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('unit_price')
                                    ->label('Unit Price (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),

                            Grid::make(4)->schema([
                                TextInput::make('total_price')
                                    ->label('Line Total (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('fulfillment_status')
                                    ->label('Fulfillment Status')
                                    ->options([
                                        'pending'   => 'Pending',
                                        'packed'    => 'Packed',
                                        'shipped'   => 'Shipped',
                                        'delivered' => 'Delivered',
                                        'returned'  => 'Returned',
                                    ]),

                                TextInput::make('courier_name')
                                    ->label('Courier')
                                    ->maxLength(100),

                                TextInput::make('tracking_number')
                                    ->label('Tracking No.')
                                    ->maxLength(100),
                            ]),
                        ])
                        ->deletable(false)
                        ->addable(false)
                        ->columnSpanFull(),
                ]),

            // ── Section 5: Financials ──────────────────────────────────
            Section::make('Financials')
                ->icon('heroicon-o-currency-rupee')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('discount_amount')
                            ->label('Discount')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('shipping_amount')
                            ->label('Shipping')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('cgst_amount')
                            ->label('CGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('sgst_amount')
                            ->label('SGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('igst_amount')
                            ->label('IGST')
                            ->prefix('₹')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    TextInput::make('total_amount')
                        ->label('Grand Total')
                        ->prefix('₹')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->extraInputAttributes(['class' => 'font-bold text-lg']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable()
                    ->url(fn(Order $record) => OrderResource::getUrl('view', ['record' => $record])),

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

                BadgeColumn::make('status')
                    ->label('Order Status')
                    ->colors([
                        'gray'    => 'pending',
                        'info'    => 'confirmed',
                        'warning' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                        'gray'    => 'refunded',
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger'  => 'failed',
                        'gray'    => 'refunded',
                    ]),

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
            ->actions([
                ViewAction::make(),

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
                        \Filament\Notifications\Notification::make()
                            ->title('Order cancelled and stock restored')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No orders found')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
```

---

### `app/Filament/Resources/OrderResource/Pages/ListOrders.php`

```php
<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        // Orders are created by customers, not by admins
        return [];
    }
}
```

---

### `app/Filament/Resources/OrderResource/Pages/ViewOrder.php`

```php
<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ViewOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_changes')
                ->label('Save Changes')
                ->action(fn() => $this->save()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Order #' . $this->record->order_number;
    }
}
```

---

## Artisan Commands

```bash
# Generate resource scaffold
php artisan make:filament-resource Order --generate

# Generate FilamentShield policies
php artisan shield:generate --resource=OrderResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **No Create page**: Orders are customer-initiated. The resource has no create route.
- **Shipping fields are read-only**: All `shipping_*` fields use `->disabled()->dehydrated(false)` to prevent modification of the snapshot. Only `status`, `payment_reference`, `paid_at`, and `notes` are editable.
- **Item `fulfillment_status`, `courier_name`, `tracking_number`** are editable directly in the repeater (vendor or admin can update per-item fulfillment).
- **Financial fields**: All financial fields (`subtotal`, `cgst`, etc.) are read-only display — they're snapshot values stored at order creation time.
- **Stock restoration on cancel**: The cancel action loops through `items` and calls `increment('stock_quantity')` on the product. Ensure the `items` relation is eager-loaded or this will cause N+1 queries.
- **Vendor filter**: Filters orders that have at least one item belonging to a given vendor, using a `whereHas` on `items.product.vendor_id`.
- **Navigation badge**: Shows count of pending orders in orange.
- **`ViewOrder` extends `EditRecord`**: This pattern allows the view page to also save changes (status updates, payment reference). Use `EditRecord` as the base class with `->disabled()` on read-only fields.
