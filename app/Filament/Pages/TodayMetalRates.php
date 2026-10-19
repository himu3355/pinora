<?php

namespace App\Filament\Pages;

use App\Filament\Resources\MetalRates\MetalRateResource;
use App\Filament\Widgets\TodayMetalRatesWidget;
use App\Models\MetalRate;
use App\Models\Setting;
use App\Services\MetalRateSyncService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class TodayMetalRates extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCurrencyRupee;

    protected static ?string $navigationLabel = "Today's Metal Rates";

    protected static ?string $title = "Today's Metal Rates Entry";

    protected static ?int $navigationSort = 24;

    protected string $view = 'filament.pages.today-metal-rates';

    public ?array $data = [];

    /**
     * Cache of latest recorded previous rates for display and pre-filling.
     * Format: ['metal_purity' => ['rate' => float, 'date' => string]]
     */
    public array $latestPreviousRates = [];

    public function mount(): void
    {
        $today = today()->toDateString();
        $this->latestPreviousRates = $this->fetchLatestPreviousRates($today);
        $this->loadRatesForDate($today, showNotification: false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Date & Reference ───────────────────────────────────────
                Section::make('Date & Market Reference')
                    ->description('Select the effective date for these rates and optional reference notes.')
                    ->icon('heroicon-o-calendar')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('effective_date')
                                ->label('Effective Date')
                                ->required()
                                ->default(today()->toDateString())
                                ->maxDate(today()->toDateString())
                                ->live()
                                ->afterStateUpdated(fn ($state) => $this->loadRatesForDate($state, showNotification: true)),

                            TextInput::make('notes')
                                ->label('Market Notes / Source')
                                ->placeholder('e.g. IBJA Opening Rate, Rajkot Bullion Association')
                                ->maxLength(255),
                        ]),
                    ]),

                // ── Gold (Au) Rates ─────────────────────────────────────────
                Section::make('Gold Rates (Au)')
                    ->description('Rates in Indian Rupees per gram for Gold alloys. Used for dynamic gold product calculations.')
                    ->icon('heroicon-o-sparkles')
                    ->headerActions([
                        Action::make('calc_gold')
                            ->label('Auto-calculate alloys from 24K')
                            ->icon('heroicon-m-calculator')
                            ->color('warning')
                            ->size('sm')
                            ->action(fn () => $this->autoCalculateGold()),
                    ])
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 2,
                            'lg'      => 4,
                        ])->schema([
                            TextInput::make('gold_24K')
                                ->label('24K Gold (Pure 999)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('gold_24K'))
                                ->helperText(fn () => $this->getHelperText('gold_24K', '99.9% Pure bullion')),

                            TextInput::make('gold_22K')
                                ->label('22K Gold (Standard 916)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('gold_22K'))
                                ->helperText(fn () => $this->getHelperText('gold_22K', '91.6% Hallmarked jewellery')),

                            TextInput::make('gold_18K')
                                ->label('18K Gold (Fine 750)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('gold_18K'))
                                ->helperText(fn () => $this->getHelperText('gold_18K', '75.0% Diamond jewellery')),

                            TextInput::make('gold_14K')
                                ->label('14K Gold (Daily 585)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('gold_14K'))
                                ->helperText(fn () => $this->getHelperText('gold_14K', '58.5% Everyday modern jewellery')),
                        ]),
                    ]),

                // ── Silver (Ag) Rates ───────────────────────────────────────
                Section::make('Silver Rates (Ag)')
                    ->description('Rates in Indian Rupees per gram for Silver alloys.')
                    ->icon('heroicon-o-circle-stack')
                    ->headerActions([
                        Action::make('calc_silver')
                            ->label('Auto-calculate 925 from 999')
                            ->icon('heroicon-m-calculator')
                            ->color('gray')
                            ->size('sm')
                            ->action(fn () => $this->autoCalculateSilver()),
                    ])
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 2,
                        ])->schema([
                            TextInput::make('silver_999')
                                ->label('999 Fine Silver (Pure)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('silver_999'))
                                ->helperText(fn () => $this->getHelperText('silver_999', '99.9% Pure silver bars & coins')),

                            TextInput::make('silver_925')
                                ->label('925 Sterling Silver')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('silver_925'))
                                ->helperText(fn () => $this->getHelperText('silver_925', '92.5% Sterling silver jewellery')),
                        ]),
                    ]),

                // ── Platinum (Pt) Rates ─────────────────────────────────────
                Section::make('Platinum Rates (Pt)')
                    ->description('Rates in Indian Rupees per gram for Platinum.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm'      => 2,
                        ])->schema([
                            TextInput::make('platinum_950')
                                ->label('950 Platinum (Pure Pt950)')
                                ->numeric()
                                ->prefix('₹')
                                ->step('0.01')
                                ->minValue(0)
                                ->placeholder($this->getPlaceholder('platinum_950'))
                                ->helperText(fn () => $this->getHelperText('platinum_950', '95.0% Fine platinum jewellery')),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TodayMetalRatesWidget::class,
        ];
    }

    public function hasSyncedToday(): bool
    {
        return app(MetalRateSyncService::class)->hasSyncedToday();
    }

    public function getLastSyncedTime(): ?string
    {
        $timestamp = app(MetalRateSyncService::class)->getLastSyncedAt();
        return $timestamp ? Carbon::parse($timestamp)->format('h:i A') : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prefill_latest')
                ->label('Pre-fill Latest Rates')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(fn () => $this->copyPreviousRates()),

            Action::make('sync_api')
                ->label(fn () => $this->hasSyncedToday() ? 'Re-sync Live Rates' : 'Sync Live Rates')
                ->icon('heroicon-o-arrow-path')
                ->color(fn () => $this->hasSyncedToday() ? 'gray' : 'success')
                ->tooltip('Fetch live market bullion rates from Gold-API.com (Free & Unlimited)')
                ->action(fn () => $this->syncFromGoldApi()),

            Action::make('view_history')
                ->label('View Rate History')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(MetalRateResource::getUrl('index')),
        ];
    }

    /**
     * Load rates for a selected date into the form.
     */
    public function loadRatesForDate(?string $date, bool $showNotification = true): void
    {
        if (blank($date)) {
            $date = today()->toDateString();
        }

        $this->latestPreviousRates = $this->fetchLatestPreviousRates($date);

        $records = MetalRate::whereDate('effective_date', $date)->get();

        $rateKeys = [
            'gold_24K', 'gold_22K', 'gold_18K', 'gold_14K',
            'silver_999', 'silver_925',
            'platinum_950',
        ];

        $formData = [
            'effective_date' => $date,
            'notes'          => $records->first()?->notes ?? ($this->data['notes'] ?? ''),
        ];

        foreach ($rateKeys as $key) {
            $formData[$key] = null;
        }

        foreach ($records as $record) {
            $key = $record->metal_type . '_' . $record->purity;
            if (in_array($key, $rateKeys)) {
                $formData[$key] = (float) $record->rate_per_gram;
            }
        }

        $this->data = $formData;
        $this->form->fill($this->data);

        if ($showNotification && $records->isNotEmpty()) {
            Notification::make()
                ->title('Loaded rates for ' . Carbon::parse($date)->format('d M Y'))
                ->info()
                ->send();
        }
    }

    /**
     * Auto-calculate 22K, 18K, and 14K Gold rates based on 24K rate.
     */
    public function autoCalculateGold(): void
    {
        $rate24 = (float) ($this->data['gold_24K'] ?? 0);

        if ($rate24 <= 0) {
            Notification::make()
                ->title('Enter 24K Gold Rate First')
                ->body('Please enter a valid rate for 24K Gold to calculate 22K, 18K, and 14K alloy rates.')
                ->warning()
                ->send();
            return;
        }

        $this->data['gold_22K'] = round($rate24 * 0.9167, 2);
        $this->data['gold_18K'] = round($rate24 * 0.7500, 2);
        $this->data['gold_14K'] = round($rate24 * 0.5833, 2);

        $this->form->fill($this->data);

        Notification::make()
            ->title('Gold alloys auto-calculated from 24K')
            ->body('Calculated: 22K (91.67%), 18K (75%), and 14K (58.33%). You may adjust any rate before saving.')
            ->success()
            ->send();
    }

    /**
     * Auto-calculate 925 Sterling Silver rate based on 999 Fine Silver rate.
     */
    public function autoCalculateSilver(): void
    {
        $rate999 = (float) ($this->data['silver_999'] ?? 0);

        if ($rate999 <= 0) {
            Notification::make()
                ->title('Enter 999 Silver Rate First')
                ->body('Please enter a valid rate for 999 Silver to calculate the 925 Sterling rate.')
                ->warning()
                ->send();
            return;
        }

        $this->data['silver_925'] = round($rate999 * 0.9250, 2);

        $this->form->fill($this->data);

        Notification::make()
            ->title('Silver 925 auto-calculated from 999')
            ->body('Calculated: 925 (92.50%). You may adjust the rate before saving.')
            ->success()
            ->send();
    }

    /**
     * Copy the most recent previous rates into the active inputs.
     */
    public function copyPreviousRates(): void
    {
        $effectiveDate = $this->data['effective_date'] ?? today()->toDateString();
        $this->latestPreviousRates = $this->fetchLatestPreviousRates($effectiveDate);

        if (empty($this->latestPreviousRates)) {
            Notification::make()
                ->title('No previous rates found')
                ->body('No prior rates were found in the database to copy.')
                ->warning()
                ->send();
            return;
        }

        foreach ($this->latestPreviousRates as $key => $info) {
            $this->data[$key] = $info['rate'];
        }

        $this->form->fill($this->data);

        Notification::make()
            ->title('Latest previous rates loaded')
            ->body('Loaded rates from previous records. Please review and adjust today\'s prices.')
            ->info()
            ->send();
    }

    /**
     * Sync rates on demand from Gold-API.com.
     */
    public function syncFromGoldApi(): void
    {
        $syncService = app(MetalRateSyncService::class);

        if ($syncService->sync()) {
            $body = $syncService->wasServedFromCache()
                ? 'Rates refreshed using cached live market prices (anti-spam protection active to prevent IP blocking).'
                : 'Live bullion market rates fetched from Gold-API.com and updated in INR/gram.';

            Notification::make()
                ->title('Rates Synced Successfully')
                ->body($body)
                ->success()
                ->send();

            $date = $this->data['effective_date'] ?? today()->toDateString();
            $this->loadRatesForDate($date, showNotification: false);
            $this->dispatch('metal-rates-updated');
        } else {
            Notification::make()
                ->title('Sync Blocked / Failed')
                ->body($syncService->getLastError() ?: 'Could not sync rates from Gold-API.com.')
                ->danger()
                ->send();
        }
    }

    /**
     * Clear all rate input fields.
     */
    public function clearForm(): void
    {
        $rateKeys = [
            'gold_24K', 'gold_22K', 'gold_18K', 'gold_14K',
            'silver_999', 'silver_925',
            'platinum_950',
        ];

        foreach ($rateKeys as $key) {
            $this->data[$key] = null;
        }

        $this->data['notes'] = null;
        $this->form->fill($this->data);

        Notification::make()
            ->title('Rate inputs cleared')
            ->info()
            ->send();
    }

    /**
     * Save all entered rates for the effective date.
     */
    public function save(bool $redirectToList = false): void
    {
        $state = $this->form->getState();
        $effectiveDate = $state['effective_date'] ?? today()->toDateString();
        $notes = $state['notes'] ?? null;

        $rateDefinitions = [
            ['metal_type' => 'gold',     'purity' => '24K', 'key' => 'gold_24K'],
            ['metal_type' => 'gold',     'purity' => '22K', 'key' => 'gold_22K'],
            ['metal_type' => 'gold',     'purity' => '18K', 'key' => 'gold_18K'],
            ['metal_type' => 'gold',     'purity' => '14K', 'key' => 'gold_14K'],
            ['metal_type' => 'silver',   'purity' => '999', 'key' => 'silver_999'],
            ['metal_type' => 'silver',   'purity' => '925', 'key' => 'silver_925'],
            ['metal_type' => 'platinum', 'purity' => '950', 'key' => 'platinum_950'],
        ];

        $savedCount = 0;
        $userId = auth()->id();

        DB::transaction(function () use ($state, $effectiveDate, $notes, $rateDefinitions, $userId, &$savedCount) {
            foreach ($rateDefinitions as $def) {
                $rateValue = $state[$def['key']] ?? null;

                if ($rateValue !== null && $rateValue !== '' && is_numeric($rateValue) && (float) $rateValue > 0) {
                    MetalRate::updateOrCreate(
                        [
                            'metal_type'     => $def['metal_type'],
                            'purity'         => $def['purity'],
                            'effective_date' => $effectiveDate,
                        ],
                        [
                            'rate_per_gram' => round((float) $rateValue, 2),
                            'updated_by'    => $userId,
                            'notes'         => $notes,
                        ]
                    );

                    MetalRate::flushRateCache($def['metal_type'], $def['purity']);
                    $savedCount++;
                }
            }
        });

        if ($savedCount === 0) {
            Notification::make()
                ->title('No rates entered')
                ->body('Please enter at least one metal rate per gram before saving.')
                ->warning()
                ->send();
            return;
        }

        $this->dispatch('metal-rates-updated');

        Notification::make()
            ->title("Saved {$savedCount} metal rates successfully")
            ->body('Rates for ' . Carbon::parse($effectiveDate)->format('d M Y') . ' are now active across the platform.')
            ->success()
            ->send();

        if ($redirectToList) {
            $this->redirect(MetalRateResource::getUrl('index'));
            return;
        }

        $this->loadRatesForDate($effectiveDate, showNotification: false);
    }

    /**
     * Query latest previous rates for reference.
     */
    protected function fetchLatestPreviousRates(string $effectiveDate): array
    {
        $purities = [
            'gold'     => ['24K', '22K', '18K', '14K'],
            'silver'   => ['999', '925'],
            'platinum' => ['950'],
        ];

        $previous = [];

        foreach ($purities as $metal => $list) {
            foreach ($list as $purity) {
                $record = MetalRate::where('metal_type', $metal)
                    ->where('purity', $purity)
                    ->where('effective_date', '<', $effectiveDate)
                    ->orderByDesc('effective_date')
                    ->first();

                // If none before effectiveDate, find latest overall
                if (!$record) {
                    $record = MetalRate::where('metal_type', $metal)
                        ->where('purity', $purity)
                        ->orderByDesc('effective_date')
                        ->first();
                }

                if ($record) {
                    $previous[$metal . '_' . $purity] = [
                        'rate' => (float) $record->rate_per_gram,
                        'date' => Carbon::parse($record->effective_date)->format('d M Y'),
                    ];
                }
            }
        }

        return $previous;
    }

    protected function getHelperText(string $key, string $baseDesc): string
    {
        $prev = $this->latestPreviousRates[$key] ?? null;

        if ($prev) {
            return "{$baseDesc} • Last recorded: ₹" . number_format($prev['rate'], 2) . "/g ({$prev['date']})";
        }

        return $baseDesc;
    }

    protected function getPlaceholder(string $key): ?string
    {
        $prev = $this->latestPreviousRates[$key] ?? null;

        if ($prev) {
            return number_format($prev['rate'], 2, '.', '');
        }

        return '0.00';
    }
}
