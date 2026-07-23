# Step 26: Admin Panel — Metal Rate Management Resource

## Goal
Create a Filament 5 Resource for daily metal rate entry. Includes a custom List page that displays a live stats panel of today's current rates at the top, and a form with dynamic purity options based on the selected metal type.

---

## Files to Create

| File | Purpose |
|------|---------|
| `app/Filament/Resources/MetalRateResource.php` | Main resource class |
| `app/Filament/Resources/MetalRateResource/Pages/ListMetalRates.php` | List page with today's rates header widget & on-demand Sync action |
| `app/Filament/Resources/MetalRateResource/Pages/CreateMetalRate.php` | Create page |
| `app/Filament/Resources/MetalRateResource/Pages/EditMetalRate.php` | Edit page |
| `app/Filament/Widgets/TodayMetalRatesWidget.php` | Header stats panel widget |
| `app/Services/MetalRateSyncService.php` | Syncs metal rates from GoldAPI on-demand |

---

## PHP Code

### `app/Filament/Resources/MetalRateResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetalRateResource\Pages;
use App\Models\MetalRate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MetalRateResource extends Resource
{
    protected static ?string $model = MetalRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Metal Rate';

    protected static ?string $pluralModelLabel = 'Metal Rates';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Metal Rate Entry')
                ->icon('heroicon-o-currency-rupee')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('metal_type')
                            ->label('Metal Type')
                            ->options([
                                'gold'     => 'Gold',
                                'silver'   => 'Silver',
                                'platinum' => 'Platinum',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(\Filament\Schemas\Components\Utilities\Set $set) => $set('purity', null)),

                        Select::make('purity')
                            ->label('Purity')
                            ->options(function (Get $get): array {
                                return match ($get('metal_type')) {
                                    'gold'     => ['24K' => '24K', '22K' => '22K', '18K' => '18K'],
                                    'silver'   => ['999' => '999', '925' => '925 (Sterling)'],
                                    'platinum' => ['950' => '950'],
                                    default    => [],
                                };
                            })
                            ->required()
                            ->disabled(fn(Get $get) => blank($get('metal_type')))
                            ->helperText('Purity options depend on the selected metal type.'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('rate_per_gram')
                            ->label('Rate Per Gram')
                            ->numeric()
                            ->prefix('₹')
                            ->required()
                            ->minValue(0)
                            ->step('0.01')
                            ->helperText('Enter the rate in Indian Rupees per gram.'),

                        DatePicker::make('effective_date')
                            ->label('Effective Date')
                            ->required()
                            ->default(today()->toDateString())
                            ->maxDate(today()->toDateString())
                            ->helperText('Usually today. Cannot be a future date.'),
                    ]),

                    TextInput::make('notes')
                        ->label('Notes')
                        ->maxLength(500)
                        ->placeholder('Optional — e.g., Source: IBJA, or market comment')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('metal_type')
                    ->label('Metal')
                    ->colors([
                        'warning' => 'gold',
                        'gray'    => 'silver',
                        'info'    => 'platinum',
                    ])
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                TextColumn::make('purity')
                    ->label('Purity')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('rate_per_gram')
                    ->label('Rate / Gram')
                    ->money('INR')
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('effective_date')
                    ->label('Effective Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('updatedBy.name')
                    ->label('Entered By')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('effective_date', 'desc')
            ->filters([
                SelectFilter::make('metal_type')
                    ->label('Metal Type')
                    ->options([
                        'gold'     => 'Gold',
                        'silver'   => 'Silver',
                        'platinum' => 'Platinum',
                    ]),

                Filter::make('today')
                    ->label("Today's Rates")
                    ->query(fn(Builder $q) => $q->whereDate('effective_date', today()))
                    ->toggle(),

                Filter::make('effective_date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'],
                                fn(Builder $q, $d) => $q->whereDate('effective_date', '>=', $d)
                            )
                            ->when($data['until'],
                                fn(Builder $q, $d) => $q->whereDate('effective_date', '<=', $d)
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No metal rates entered')
            ->emptyStateDescription('Add today\'s rates to enable dynamic pricing.')
            ->emptyStateIcon('heroicon-o-currency-rupee');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMetalRates::route('/'),
            'create' => Pages\CreateMetalRate::route('/create'),
            'edit'   => Pages\EditMetalRate::route('/{record}/edit'),
        ];
    }
}
```

---

### `app/Filament/Widgets/TodayMetalRatesWidget.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\MetalRate;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayMetalRatesWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $today = today()->toDateString();

        $rates = MetalRate::whereDate('effective_date', $today)
            ->orderBy('metal_type')
            ->orderBy('purity')
            ->get()
            ->keyBy(fn(MetalRate $r) => $r->metal_type . '_' . $r->purity);

        $format = fn(?MetalRate $rate): string =>
            $rate ? '₹' . number_format($rate->rate_per_gram, 2) . ' / g' : 'Not set';

        $gold24 = $rates->get('gold_24K');
        $gold22 = $rates->get('gold_22K');
        $gold18 = $rates->get('gold_18K');
        $silver999 = $rates->get('silver_999');
        $silver925 = $rates->get('silver_925');
        $platinum950 = $rates->get('platinum_950');

        $stats = [];

        if ($gold24 || $gold22 || $gold18) {
            if ($gold24) {
                $stats[] = Stat::make('Gold 24K', $format($gold24))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('warning');
            }
            if ($gold22) {
                $stats[] = Stat::make('Gold 22K', $format($gold22))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('warning');
            }
            if ($gold18) {
                $stats[] = Stat::make('Gold 18K', $format($gold18))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('warning');
            }
        }

        if ($silver999 || $silver925) {
            if ($silver999) {
                $stats[] = Stat::make('Silver 999', $format($silver999))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('gray');
            }
            if ($silver925) {
                $stats[] = Stat::make('Silver 925', $format($silver925))
                    ->description('Today — ' . today()->format('d M Y'))
                    ->color('gray');
            }
        }

        if ($platinum950) {
            $stats[] = Stat::make('Platinum 950', $format($platinum950))
                ->description('Today — ' . today()->format('d M Y'))
                ->color('info');
        }

        if (empty($stats)) {
            $stats[] = Stat::make("Today's Rates", 'Not yet entered')
                ->description('Add today\'s rates using the button above.')
                ->color('danger')
                ->descriptionIcon('heroicon-m-exclamation-triangle');
        }

        return $stats;
    }
}
```

---

### `app/Filament/Resources/MetalRateResource/Pages/ListMetalRates.php`

```php
<?php

namespace App\Filament\Resources\MetalRateResource\Pages;

use App\Filament\Resources\MetalRateResource;
use App\Filament\Widgets\TodayMetalRatesWidget;
use App\Services\MetalRateSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMetalRates extends ListRecords
{
    protected static string $resource = MetalRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Today\'s Rate'),
            Action::make('sync')
                ->label('Sync Daily Rates')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function (MetalRateSyncService $syncService) {
                    if ($syncService->sync()) {
                        Notification::make()
                            ->title('Rates Synced Successfully')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Could not sync rates. Using last successfully stored rates.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TodayMetalRatesWidget::class,
        ];
    }
}
```

---

### `app/Filament/Resources/MetalRateResource/Pages/CreateMetalRate.php`

```php
<?php

namespace App\Filament\Resources\MetalRateResource\Pages;

use App\Filament\Resources\MetalRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMetalRate extends CreateRecord
{
    protected static string $resource = MetalRateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

### `app/Filament/Resources/MetalRateResource/Pages/EditMetalRate.php`

```php
<?php

namespace App\Filament\Resources\MetalRateResource\Pages;

use App\Filament\Resources\MetalRateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMetalRate extends EditRecord
{
    protected static string $resource = MetalRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

### `app/Services/MetalRateSyncService.php`

```php
<?php

namespace App\Services;

use App\Models\MetalRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetalRateSyncService
{
    /**
     * Fetch daily metal rates from GoldAPI and store them.
     */
    public function sync(): bool
    {
        try {
            $apiKey = config('services.goldapi.key');
            if (blank($apiKey)) {
                Log::warning('MetalRateSyncService: GoldAPI token is not configured.');
                return false;
            }

            // 1. Sync Gold (XAU) 24K in INR
            $goldResponse = Http::withHeaders([
                'x-access-token' => $apiKey
            ])->get('https://www.goldapi.io/api/XAU/INR');

            if ($goldResponse->successful()) {
                $goldData = $goldResponse->json();
                $rate24K = $goldData['price_gram_24k'] ?? null;
                
                if ($rate24K) {
                    // Save 24K rate
                    $this->updateRate('gold', '24K', $rate24K);
                    // Calculate 22K (91.67%), 18K (75%), 14K (58.33%) standard purity ratios
                    $this->updateRate('gold', '22K', $rate24K * 0.9167);
                    $this->updateRate('gold', '18K', $rate24K * 0.75);
                }
            } else {
                Log::warning('MetalRateSyncService: Gold price fetch failed with response ' . $goldResponse->status());
            }

            // 2. Sync Silver (XAG) in INR
            $silverResponse = Http::withHeaders([
                'x-access-token' => $apiKey
            ])->get('https://www.goldapi.io/api/XAG/INR');

            if ($silverResponse->successful()) {
                $silverData = $silverResponse->json();
                // GoldAPI returns price per gram for silver 24k (pure silver) under price_gram_24k
                $ratePureSilver = $silverData['price_gram_24k'] ?? null;

                if ($ratePureSilver) {
                    $this->updateRate('silver', '999', $ratePureSilver);
                    $this->updateRate('silver', '925', $ratePureSilver * 0.925);
                }
            } else {
                Log::warning('MetalRateSyncService: Silver price fetch failed with response ' . $silverResponse->status());
            }

            return true;
        } catch (\Exception $e) {
            Log::error('MetalRateSyncService exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function updateRate(string $metalType, string $purity, float $ratePerGram): void
    {
        MetalRate::updateOrCreate(
            [
                'metal_type' => $metalType,
                'purity' => $purity,
                'effective_date' => today()->toDateString(),
            ],
            [
                'rate_per_gram' => round($ratePerGram, 2),
                'notes' => 'Synced via GoldAPI on ' . now()->toDateTimeString(),
            ]
        );
    }
}
```

---

## Artisan Commands

```bash
# Generate resource scaffold
php artisan make:filament-resource MetalRate --generate

# Generate the TodayMetalRatesWidget
php artisan make:filament-widget TodayMetalRatesWidget --stats-overview

# Generate FilamentShield policies
php artisan shield:generate --resource=MetalRateResource

# Clear cache
php artisan filament:cache-components
```

---

## Notes

- **Dynamic purity options**: The purity `Select` uses `->live()` on `metal_type` and `Get $get` in the options closure to dynamically filter purity values. The purity field is also disabled until a metal type is selected.
- **`updated_by` field**: The `CreateMetalRate` and `EditMetalRate` pages inject `auth()->id()` into `updated_by` before saving. Ensure the `metal_rates` table has an `updated_by` foreign key column.
- **`updatedBy` relation**: The `MetalRate` model needs `public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }`.
- **Header widget**: `TodayMetalRatesWidget` is embedded in `ListMetalRates` via `getHeaderWidgets()` — it appears above the table, not in the global dashboard.
- **Polling**: The widget refreshes every 60 seconds (`$pollingInterval = '60s'`) — helpful if rates are being entered by multiple admins.
- **Price formula**: The metal rate is referenced by the product price calculation service. The service should query `MetalRate::whereDate('effective_date', today())->where('metal_type', $type)->where('purity', $purity)->latest()->first()` to get the current rate.
- **No future dates**: The effective_date `DatePicker` has `->maxDate(today())` to prevent pre-entering rates for future dates.
