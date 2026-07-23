# Step 36: Vendor Panel — Customer View

**Goal:** Create a read-only Filament Resource for vendors to view profiles of customers who have ordered their products, including contact details, addresses, and their order history with the vendor.
**Depends On:** Step 33 (Vendor Panel Setup), Step 12 (Update User Model), Step 20 (Order & OrderItem Models)
**Next Step:** Step 37 (Vendor Payout Reports)

---

## Goal Explanation

Vendors need to see details of customers who have ordered from them for shipping coordination, service inquiries, and relationship management. However, vendors should only see customers who have placed orders containing their items.

This step implements the `VendorCustomerResource` inside the Vendor panel. Key specifications:
1. **Scope:** Only returns `User` records having orders that contain this vendor's items.
2. **Aggregated Columns:** The table lists customer names, emails, phones, plus aggregated stats: total orders count, total spent, and last order date—all scoped exclusively to that vendor's items.
3. **Relation Managers:**
   - Shows the customer's addresses.
   - Shows a list of order items containing this vendor's products (not other vendors' products).
4. **Permissions:** Fully read-only. No create, edit, or delete capability.

---

## Files to Create

1. `app/Filament/Vendor/Resources/VendorCustomerResource.php`
2. `app/Filament/Vendor/Resources/VendorCustomerResource/Pages/ListVendorCustomers.php`
3. `app/Filament/Vendor/Resources/VendorCustomerResource/Pages/ViewVendorCustomer.php`
4. `app/Filament/Vendor/Resources/VendorCustomerResource/RelationManagers/OrderItemsRelationManager.php`

---

## Complete PHP Code

### 1. `app/Filament/Vendor/Resources/VendorCustomerResource.php`

```php
<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\VendorCustomerResource\Pages;
use App\Filament\Vendor\Resources\VendorCustomerResource\RelationManagers;
use App\Models\User;
use App\Services\VendorContext;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorCustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $pluralLabel = 'Customers';

    protected static ?int $navigationSort = 3;

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

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Read-only resource, no form needed
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('vendor_orders_count')
                    ->label('My Orders')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_orders_count', $direction))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('vendor_total_spent')
                    ->label('Total Sales')
                    ->money('INR')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_total_spent', $direction)),

                Tables\Columns\TextColumn::make('vendor_last_order_date')
                    ->label('Last Order')
                    ->dateTime('d M Y')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('vendor_last_order_date', $direction))
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Customer Profile')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\ImageEntry::make('avatar_url')
                                ->label('Avatar')
                                ->circular()
                                ->disk('public'),

                            Infolists\Components\TextEntry::make('name')
                                ->label('Full Name')
                                ->weight('bold'),

                            Infolists\Components\TextEntry::make('email')
                                ->label('Email Address')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('phone')
                                ->label('Phone Number')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('gender')
                                ->label('Gender')
                                ->badge()
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('birthday')
                                ->label('Birthday')
                                ->date('d M Y')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('anniversary_date')
                                ->label('Anniversary')
                                ->date('d M Y')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('ring_size')
                                ->label('Ring Size')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('bangle_size')
                                ->label('Bangle Size')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorCustomers::route('/'),
            'view' => Pages\ViewVendorCustomer::route('/{record}'),
        ];
    }
}
```

---

### 2. `app/Filament/Vendor/Resources/VendorCustomerResource/Pages/ListVendorCustomers.php`

```php
<?php

namespace App\Filament\Vendor\Resources\VendorCustomerResource\Pages;

use App\Filament\Vendor\Resources\VendorCustomerResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorCustomers extends ListRecords
{
    protected static string $resource = VendorCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

---

### 3. `app/Filament/Vendor/Resources/VendorCustomerResource/Pages/ViewVendorCustomer.php`

```php
<?php

namespace App\Filament\Vendor\Resources\VendorCustomerResource\Pages;

use App\Filament\Vendor\Resources\VendorCustomerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorCustomer extends ViewRecord
{
    protected static string $resource = VendorCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

---

### 4. `app/Filament/Vendor/Resources/VendorCustomerResource/RelationManagers/OrderItemsRelationManager.php`

```php
<?php

namespace App\Filament\Vendor\Resources\VendorCustomerResource\RelationManagers;

use App\Models\OrderItem;
use App\Services\VendorContext;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
        // order items that belong to them.
        return $table
            ->recordTitleAttribute('order_number')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereHas('items', fn ($q) => $q->where('vendor_id', $vendorId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d M Y h:i A'),

                Tables\Columns\TextColumn::make('items')
                    ->label('Products Ordered')
                    ->state(function ($record) use ($vendorId) {
                        return $record->items()
                            ->where('vendor_id', $vendorId)
                            ->get()
                            ->map(fn (OrderItem $item) => "{$item->product_name} (x{$item->quantity})")
                            ->join(', ');
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('vendor_earnings')
                    ->label('My Earnings')
                    ->money('INR')
                    ->state(function ($record) use ($vendorId) {
                        return $record->items()
                            ->where('vendor_id', $vendorId)
                            ->sum('total_price');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                \Filament\Actions\Action::make('view_order_item')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => route('filament.vendor.resources.order-items.view', [
                        'record' => $record->items()->where('vendor_id', $vendorId)->first()?->id ?? 0
                    ])),
            ])
            ->bulkActions([]);
    }
}
```

---

## Notes

- **Aggregated Sorting:** By default, subquery columns like `vendor_total_spent` cannot be sorted automatically in Laravel/Filament. We solve this by adding explicit query closures to `sortable()` that order the records by their calculated aliases.
- **Privacy Enforcement:** In the `OrderItemsRelationManager`, we constrain the query to only show orders containing this vendor's items. We also aggregate "Products Ordered" and "My Earnings" by applying the `vendor_id` filter to ensure vendors cannot see information about items supplied by other vendors in the same order.
- **Deep Linking:** The custom relation manager action `view_order_item` redirects the vendor directly to the detailed `OrderItemResource` view page for their specific item from that order.
