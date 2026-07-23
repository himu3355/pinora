# Step 31: Admin Panel — Commission & Payouts Resources

## Goal
Create two Filament 5 Resources:
1. **CommissionResource** — read-only view of auto-generated commission records
2. **PayoutResource** — create and manage vendor payout batches, with auto-calculation of totals from pending commissions

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/CommissionResource.php` | Commission read-only resource |
| `app/Filament/Resources/CommissionResource/Pages/ListCommissions.php` | Commission list page |
| `app/Filament/Resources/PayoutResource.php` | Payout management resource |
| `app/Filament/Resources/PayoutResource/Pages/ListPayouts.php` | Payout list page |
| `app/Filament/Resources/PayoutResource/Pages/CreatePayout.php` | Payout create page |
| `app/Filament/Resources/PayoutResource/Pages/ViewPayout.php` | Payout view page |

---

## PHP Code

### `app/Filament/Resources/CommissionResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\Commission;
use App\Models\Vendor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = 'Commission';

    protected static ?string $pluralModelLabel = 'Commissions';

    // Commissions are read-only — no form needed
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orderItem.order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('orderItem.product_name')
                    ->label('Product')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('vendor.store_name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('order_amount')
                    ->label('Order Amount')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('commission_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('INR')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('vendor_earnings')
                    ->label('Vendor Earns')
                    ->money('INR')
                    ->sortable()
                    ->color('success'),

                TextColumn::make('payout.payout_reference')
                    ->label('Payout')
                    ->placeholder('Not yet paid')
                    ->badge()
                    ->color(fn(?string $state) => $state ? 'success' : 'gray'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'gray'    => 'on_hold',
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid'    => 'Paid',
                        'on_hold' => 'On Hold',
                    ]),

                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(
                        Vendor::where('status', 'approved')
                            ->orderBy('store_name')
                            ->pluck('store_name', 'id')
                    )
                    ->searchable(),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Until'),
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
                // Read-only — no actions
            ])
            ->bulkActions([])
            ->emptyStateHeading('No commissions yet')
            ->emptyStateDescription('Commissions are generated automatically when orders are placed.')
            ->emptyStateIcon('heroicon-o-percent-badge');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
        ];
    }

    // No create/edit/delete permissions needed
    public static function canCreate(): bool
    {
        return false;
    }
}
```

---

### `app/Filament/Resources/CommissionResource/Pages/ListCommissions.php`

```php
<?php

namespace App\Filament\Resources\CommissionResource\Pages;

use App\Filament\Resources\CommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;

    protected function getHeaderActions(): array
    {
        return []; // No create button — commissions are auto-generated
    }
}
```

---

### `app/Filament/Resources/PayoutResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutResource\Pages;
use App\Models\Commission;
use App\Models\Payout;
use App\Models\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 75;

    protected static ?string $modelLabel = 'Payout';

    protected static ?string $pluralModelLabel = 'Payouts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Payout Details')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(
                                Vendor::where('status', 'approved')
                                    ->orderBy('store_name')
                                    ->pluck('store_name', 'id')
                            )
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get): void {
                                // Recalculate when vendor changes
                                self::recalculateTotals($set, $get);
                            }),

                        TextInput::make('payout_reference')
                            ->label('Payout Reference')
                            ->maxLength(100)
                            ->placeholder('Auto-generated on save')
                            ->disabled(fn(string $operation) => $operation !== 'edit'),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('period_from')
                            ->label('Period From')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::recalculateTotals($set, $get)),

                        DatePicker::make('period_to')
                            ->label('Period To')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::recalculateTotals($set, $get)),
                    ]),
                ]),

            Section::make('Calculated Totals')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('total_orders_amount')
                            ->label('Total Order Amount (in period)')
                            ->content(function (Get $get): string {
                                $amount = self::getPendingCommissionsQuery($get)->sum('order_amount');
                                return '₹' . number_format($amount, 2);
                            }),

                        Placeholder::make('total_commission')
                            ->label('Platform Commission Deducted')
                            ->content(function (Get $get): string {
                                $amount = self::getPendingCommissionsQuery($get)->sum('commission_amount');
                                return '₹' . number_format($amount, 2);
                            }),

                        Placeholder::make('total_vendor_earnings')
                            ->label('Vendor Earnings (before adjustments)')
                            ->content(function (Get $get): string {
                                $amount = self::getPendingCommissionsQuery($get)->sum('vendor_earnings');
                                return '₹' . number_format($amount, 2);
                            }),

                        Placeholder::make('commission_count')
                            ->label('Number of Commissions Included')
                            ->content(function (Get $get): string {
                                return (string) self::getPendingCommissionsQuery($get)->count();
                            }),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('adjustments')
                            ->label('Adjustments (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->helperText('Use negative for deductions. E.g., -500 for a penalty.')
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::recalculateTotals($set, $get)),

                        TextInput::make('final_payout_amount')
                            ->label('Final Payout Amount (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(true)
                            ->helperText('Vendor earnings + adjustments'),
                    ]),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(2)
                        ->maxLength(1000)
                        ->placeholder('Optional internal notes for this payout')
                        ->columnSpanFull(),
                ]),

            Section::make('Payment Details')
                ->icon('heroicon-o-credit-card')
                ->collapsible()
                ->visible(fn(string $operation) => $operation === 'edit')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->maxLength(255)
                            ->placeholder('Bank transfer ref, UTR, etc.'),

                        DatePicker::make('paid_at')
                            ->label('Paid At'),
                    ]),
                ]),
        ]);
    }

    /**
     * Returns the Commission query for pending commissions
     * matching the selected vendor + date range.
     */
    private static function getPendingCommissionsQuery(Get $get): \Illuminate\Database\Eloquent\Builder
    {
        $vendorId   = $get('vendor_id');
        $periodFrom = $get('period_from');
        $periodTo   = $get('period_to');

        return Commission::where('status', 'pending')
            ->when($vendorId,   fn(Builder $q) => $q->where('vendor_id', $vendorId))
            ->when($periodFrom, fn(Builder $q) => $q->whereDate('created_at', '>=', $periodFrom))
            ->when($periodTo,   fn(Builder $q) => $q->whereDate('created_at', '<=', $periodTo));
    }

    /**
     * Recalculate and set the final_payout_amount field.
     */
    private static function recalculateTotals(Set $set, Get $get): void
    {
        $vendorEarnings = self::getPendingCommissionsQuery($get)->sum('vendor_earnings');
        $adjustments    = (float) ($get('adjustments') ?? 0);
        $final          = $vendorEarnings + $adjustments;
        $set('final_payout_amount', round($final, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payout_reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('vendor.store_name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('period_from')
                    ->label('Period From')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('period_to')
                    ->label('Period To')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('final_payout_amount')
                    ->label('Payout Amount')
                    ->money('INR')
                    ->sortable()
                    ->weight('semibold'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'info'    => 'processing',
                        'gray'    => 'cancelled',
                    ]),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'paid'       => 'Paid',
                        'cancelled'  => 'Cancelled',
                    ]),

                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(
                        Vendor::where('status', 'approved')
                            ->orderBy('store_name')
                            ->pluck('store_name', 'id')
                    )
                    ->searchable(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Created From'),
                        DatePicker::make('until')->label('Created Until'),
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

                Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(Payout $record) => $record->status !== 'paid')
                    ->form([
                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Bank UTR number or transfer reference'),

                        DatePicker::make('paid_at')
                            ->label('Payment Date')
                            ->required()
                            ->default(today()->toDateString()),
                    ])
                    ->action(function (Payout $record, array $data): void {
                        $record->update([
                            'status'            => 'paid',
                            'payment_reference' => $data['payment_reference'],
                            'paid_at'           => $data['paid_at'],
                        ]);

                        // Mark all associated commissions as paid
                        Commission::where('payout_id', $record->id)
                            ->update(['status' => 'paid']);

                        \Filament\Notifications\Notification::make()
                            ->title('Payout marked as paid')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No payouts yet')
            ->emptyStateDescription('Create a payout to batch vendor earnings.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayouts::route('/'),
            'create' => Pages\CreatePayout::route('/create'),
            'view'   => Pages\ViewPayout::route('/{record}'),
        ];
    }
}
```

---

### `app/Filament/Resources/PayoutResource/Pages/ListPayouts.php`

```php
<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Payout'),
        ];
    }
}
```

---

### `app/Filament/Resources/PayoutResource/Pages/CreatePayout.php`

```php
<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use App\Models\Commission;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePayout extends CreateRecord
{
    protected static string $resource = PayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate payout reference
        $data['payout_reference'] = 'PAY-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Associate pending commissions for this vendor/period with this payout
        Commission::where('status', 'pending')
            ->where('vendor_id', $this->record->vendor_id)
            ->whereDate('created_at', '>=', $this->record->period_from)
            ->whereDate('created_at', '<=', $this->record->period_to)
            ->update(['payout_id' => $this->record->id]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
```

---

### `app/Filament/Resources/PayoutResource/Pages/ViewPayout.php`

```php
<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use App\Models\Commission;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class ViewPayout extends EditRecord
{
    protected static string $resource = PayoutResource::class;

    public function getTitle(): string
    {
        return 'Payout: ' . $this->record->payout_reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_paid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn() => $this->record->status !== 'paid')
                ->form([
                    TextInput::make('payment_reference')
                        ->label('Payment Reference')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('paid_at')
                        ->label('Payment Date')
                        ->required()
                        ->default(today()->toDateString()),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'            => 'paid',
                        'payment_reference' => $data['payment_reference'],
                        'paid_at'           => $data['paid_at'],
                    ]);
                    Commission::where('payout_id', $this->record->id)
                        ->update(['status' => 'paid']);
                    $this->refreshFormData(['status', 'paid_at', 'payment_reference']);
                    \Filament\Notifications\Notification::make()
                        ->title('Payout marked as paid')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

## Artisan Commands

```bash
# Generate CommissionResource scaffold
php artisan make:filament-resource Commission --generate

# Generate PayoutResource scaffold
php artisan make:filament-resource Payout --generate

# Generate FilamentShield policies
php artisan shield:generate --resource=CommissionResource
php artisan shield:generate --resource=PayoutResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **Commission auto-creation**: Commissions should be created by an `OrderObserver` or event listener when an order's `payment_status` changes to `paid`. The commission record should store: `vendor_id`, `order_item_id`, `order_amount`, `commission_rate` (from the vendor's current rate), `commission_amount`, `vendor_earnings`, `status = pending`.
- **Live recalculation**: The `Placeholder` components in the payout form use `Get $get` to query the DB live when vendor/date fields change. This gives the admin a real-time preview of what commissions will be included.
- **`payout_id` on commissions**: The `commissions` table needs a nullable `payout_id` foreign key. After a payout is created, `CreatePayout::afterCreate()` bulk-updates matching commissions to link them.
- **`adjustments` field**: Can be positive (bonus) or negative (deduction). The `final_payout_amount` is computed as `vendor_earnings + adjustments`.
- **`payout_reference` auto-generation**: Generated in `mutateFormDataBeforeCreate` using a random string + date suffix. E.g., `PAY-ABCD1234-20260621`.
- **Mark as Paid cascade**: When a payout is marked as paid (either from table action or view page), all linked commissions are also marked as paid via a bulk update.
- **`CommissionResource::canCreate()`**: Returns `false` to remove the create button — commissions are system-generated only.
