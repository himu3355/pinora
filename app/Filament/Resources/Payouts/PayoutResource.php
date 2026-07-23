<?php

namespace App\Filament\Resources\Payouts;

use App\Filament\Resources\Payouts\Pages\CreatePayout;
use App\Filament\Resources\Payouts\Pages\ListPayouts;
use App\Filament\Resources\Payouts\Pages\ViewPayout;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Vendor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // <--- Hides from sidebar
    
    protected static ?string $model = Payout::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 75;

    protected static ?string $modelLabel = 'Payout';

    protected static ?string $pluralModelLabel = 'Payouts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payout Details')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    FormGrid::make(2)->schema([
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
                                self::recalculateTotals($set, $get);
                            }),

                        TextInput::make('payout_reference')
                            ->label('Payout Reference')
                            ->maxLength(100)
                            ->placeholder('Auto-generated on save')
                            ->disabled(fn(string $operation) => $operation !== 'edit'),
                    ]),

                    FormGrid::make(2)->schema([
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
                    FormGrid::make(2)->schema([
                        Placeholder::make('total_orders_amount')
                            ->label('Total Order Amount (in period)')
                            ->content(function (Get $get): string {
                                $amount = self::getPendingOrdersQuery($get)->sum('total_price');
                                return '₹' . number_format($amount, 2);
                            }),

                        Placeholder::make('total_vendor_earnings')
                            ->label('Vendor Earnings (100% of Orders)')
                            ->content(function (Get $get): string {
                                $amount = self::getPendingOrdersQuery($get)->sum('total_price');
                                return '₹' . number_format($amount, 2);
                            }),
                    ]),

                    FormGrid::make(2)->schema([
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
                    FormGrid::make(2)->schema([
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

    private static function getPendingOrdersQuery(Get $get): Builder
    {
        $vendorId   = $get('vendor_id');
        $periodFrom = $get('period_from');
        $periodTo   = $get('period_to');

        return OrderItem::query()
            ->where('fulfillment_status', '!=', 'cancelled')
            ->when($vendorId,   fn(Builder $q) => $q->where('vendor_id', $vendorId))
            ->when($periodFrom, fn(Builder $q) => $q->whereDate('created_at', '>=', $periodFrom))
            ->when($periodTo,   fn(Builder $q) => $q->whereDate('created_at', '<=', $periodTo));
    }

    private static function recalculateTotals(Set $set, Get $get): void
    {
        $vendorEarnings = self::getPendingOrdersQuery($get)->sum('total_price');
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'     => 'warning',
                        'processed' => 'info',
                        'paid'      => 'success',
                        'failed'    => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

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
                        'draft'     => 'Draft',
                        'processed' => 'Processed',
                        'paid'      => 'Paid',
                        'failed'    => 'Failed',
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

                        Notification::make()
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
            'edit'   => Pages\ViewPayout::route('/{record}/edit'), // Add edit route
        ];
    }
}
