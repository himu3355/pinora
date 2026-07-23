# Step 37: Vendor Panel — Payout Reports

**Goal:** Create read-only Filament resources for vendors to track their earnings, payouts, and commissions, complete with a breakdown of settlements and pending balances.
**Depends On:** Step 33 (Vendor Panel Setup), Step 21 (Commission & Payout Models)
**Next Step:** Step 38 (Vendor Panel Dashboard Widgets)

---

## Goal Explanation

Vendors need clear, transparent reporting on their financial settlements. This step implements two read-only Filament Resources in the Vendor panel:

1. **`PayoutResource`**:
   - Lists past payouts made to the vendor.
   - Shows settlement status, dates, periods, bank accounts used, and notes.
   - The view page includes a `CommissionsRelationManager` showing all order items included in that specific payout.
2. **`CommissionResource`**:
   - Lists commissions generated for each delivered order item.
   - Shows the order number, product name, commission rate applied, commission amount deducted by the platform, and the final vendor earnings.
   - Embeds a header widget at the top of the list page showing the vendor's total **Pending Balance** (unpaid earnings) and **Paid Earnings**.

Both resources are read-only to ensure vendors cannot create or modify transactions.

---

## Files to Create

1. `app/Filament/Vendor/Resources/PayoutResource.php`
2. `app/Filament/Vendor/Resources/PayoutResource/Pages/ListPayouts.php`
3. `app/Filament/Vendor/Resources/PayoutResource/Pages/ViewPayout.php`
4. `app/Filament/Vendor/Resources/PayoutResource/RelationManagers/CommissionsRelationManager.php`
5. `app/Filament/Vendor/Resources/CommissionResource.php`
6. `app/Filament/Vendor/Resources/CommissionResource/Pages/ListCommissions.php`
7. `app/Filament/Vendor/Resources/CommissionResource/Widgets/CommissionStatsOverview.php`

---

## Complete PHP Code

### 1. `app/Filament/Vendor/Resources/PayoutResource.php`

```php
<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\PayoutResource\Pages;
use App\Filament\Vendor\Resources\PayoutResource\RelationManagers;
use App\Models\Payout;
use App\Services\VendorContext;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payout History';

    protected static ?string $pluralLabel = 'Payouts';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Read-only
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payout_reference')
                    ->label('Payout Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('period_from')
                    ->label('Period From')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('period_to')
                    ->label('Period To')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_orders_amount')
                    ->label('Sales')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Commission')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_payout_amount')
                    ->label('Final Payout')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processed' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payout Details')
                    ->schema([
                        Infolists\Components\Grid::make(4)->schema([
                            Infolists\Components\TextEntry::make('payout_reference')
                                ->label('Reference ID')
                                ->weight('bold'),

                            Infolists\Components\TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'draft' => 'gray',
                                    'processed' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    default => 'gray',
                                }),

                            Infolists\Components\TextEntry::make('period_from')
                                ->label('Start Date')
                                ->date('d M Y'),

                            Infolists\Components\TextEntry::make('period_to')
                                ->label('End Date')
                                ->date('d M Y'),
                        ]),
                    ]),

                Infolists\Components\Grid::make(3)->schema([
                    Infolists\Components\Section::make('Financial Overview')
                        ->columnSpan(2)
                        ->schema([
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('total_orders_amount')
                                    ->label('Total Order Sales')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('total_commission')
                                    ->label('Total Platform Commission')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('total_vendor_earnings')
                                    ->label('Vendor Net Earnings')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('adjustments')
                                    ->label('Adjustments')
                                    ->money('INR')
                                    ->helperText('Credits / Debits applied by admin'),

                                Infolists\Components\TextEntry::make('final_payout_amount')
                                    ->label('Final Payout Amount')
                                    ->money('INR')
                                    ->weight('bold'),
                            ]),
                        ]),

                    Infolists\Components\Section::make('Settlement Details')
                        ->columnSpan(1)
                        ->schema([
                            Infolists\Components\TextEntry::make('paid_at')
                                ->label('Paid At')
                                ->dateTime('d M Y h:i A')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('payment_reference')
                                ->label('UTR / Bank UTR Reference')
                                ->placeholder('—')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('notes')
                                ->label('Settlement Notes')
                                ->placeholder('—'),
                        ]),
                ]),

                Infolists\Components\Section::make('Settled Bank Account')
                    ->schema([
                        Infolists\Components\Grid::make(4)->schema([
                            Infolists\Components\TextEntry::make('bank_account_name')
                                ->label('Account Holder Name')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('bank_name')
                                ->label('Bank Name')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('bank_account_number')
                                ->label('Account Number')
                                ->placeholder('—')
                                ->state(fn ($record) => $record->bank_account_number ? '••••' . substr($record->bank_account_number, -4) : '—'),

                            Infolists\Components\TextEntry::make('bank_ifsc_code')
                                ->label('IFSC Code')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayouts::route('/'),
            'view' => Pages\ViewPayout::route('/{record}'),
        ];
    }
}
```

---

### 2. `app/Filament/Vendor/Resources/PayoutResource/Pages/ListPayouts.php`

```php
<?php

namespace App\Filament\Vendor\Resources\PayoutResource\Pages;

use App\Filament\Vendor\Resources\PayoutResource;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

---

### 3. `app/Filament/Vendor/Resources/PayoutResource/Pages/ViewPayout.php`

```php
<?php

namespace App\Filament\Vendor\Resources\PayoutResource\Pages;

use App\Filament\Vendor\Resources\PayoutResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPayout extends ViewRecord
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

---

### 4. `app/Filament/Vendor/Resources/PayoutResource/RelationManagers/CommissionsRelationManager.php`

```php
<?php

namespace App\Filament\Vendor\Resources\PayoutResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissions';

    protected static ?string $title = 'Included Order Items';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.order.order_number')
                    ->label('Order No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('orderItem.product_name')
                    ->label('Product')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('order_amount')
                    ->label('Sales Amount')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission Rate')
                    ->suffix('%')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission Amount')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('vendor_earnings')
                    ->label('Vendor Share')
                    ->money('INR')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'included_in_payout' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
```

---

### 5. `app/Filament/Vendor/Resources/CommissionResource.php`

```php
<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\CommissionResource\Pages;
use App\Models\Commission;
use App\Services\VendorContext;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Commission Reports';

    protected static ?string $pluralLabel = 'Commissions';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['orderItem.order'])
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Read-only
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.order.order_number')
                    ->label('Order Details')
                    ->description(fn (Commission $record): string => $record->orderItem->product_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('orderItem.order', function ($q) use ($search) {
                            $q->where('order_number', 'like', "%{$search}%");
                        })->orWhereHas('orderItem', function ($q) use ($search) {
                            $q->where('product_name', 'like', "%{$search}%");
                        });
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order_amount')
                    ->label('Order Amount')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor_earnings')
                    ->label('My Earnings')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'included_in_payout' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'included_in_payout' => 'Included in Payout',
                        'paid' => 'Paid',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
        ];
    }
}
```

---

### 6. `app/Filament/Vendor/Resources/CommissionResource/Pages/ListCommissions.php`

```php
<?php

namespace App\Filament\Vendor\Resources\CommissionResource\Pages;

use App\Filament\Vendor\Resources\CommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CommissionResource\Widgets\CommissionStatsOverview::class,
        ];
    }
}
```

---

### 7. `app/Filament/Vendor/Resources/CommissionResource/Widgets/CommissionStatsOverview.php`

```php
<?php

namespace App\Filament\Vendor\Resources\CommissionResource\Widgets;

use App\Models\Commission;
use App\Services\VendorContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommissionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $vendorId = VendorContext::currentId();

        $pendingAmount = Commission::where('vendor_id', $vendorId)
            ->where('status', 'pending')
            ->sum('vendor_earnings');

        $paidAmount = Commission::where('vendor_id', $vendorId)
            ->where('status', 'paid')
            ->sum('vendor_earnings');

        return [
            Stat::make('Pending Balance', '₹' . number_format($pendingAmount, 2))
                ->description('Unsettled earnings from delivered orders')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Settled & Paid', '₹' . number_format($paidAmount, 2))
                ->description('Total earnings paid out by platform')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
```

---

## Notes

- **Compound Columns:** The Commission list uses the `description()` attribute to stack the order number and the product name in a single cell, keeping the table compact and readable.
- **Dynamic Bank Hiding:** The `PayoutResource` infolist masks the bank account number, showing only the last 4 digits (`••••1234`) for security, even though the field is automatically decrypted under the hood.
- **Filters Scope:** All widgets and resources query within `VendorContext::currentId()`, preventing cross-tenant leakage.
