<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\PayoutResource\Pages\ListPayouts;
use App\Filament\Vendor\Resources\PayoutResource\Pages\ViewPayout;
use App\Models\Payout;
use App\Services\VendorContext;
use BackedEnum;
use Filament\Schemas\Components\Grid as InfoGrid;
use Filament\Schemas\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Payout History';

    protected static ?string $pluralLabel = 'Payouts';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', VendorContext::currentId());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]); // Read-only
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payout_reference')
                    ->label('Payout Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('period_from')
                    ->label('Period From')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('period_to')
                    ->label('Period To')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_orders_amount')
                    ->label('Sales')
                    ->money('INR')
                    ->sortable(),


                TextColumn::make('final_payout_amount')
                    ->label('Final Payout')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processed' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? ''))
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                InfoSection::make('Payout Details')
                    ->schema([
                        InfoGrid::make(4)->schema([
                            TextEntry::make('payout_reference')
                                ->label('Reference ID')
                                ->weight('bold'),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'draft' => 'gray',
                                    'processed' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                            TextEntry::make('period_from')
                                ->label('Start Date')
                                ->date('d M Y'),

                            TextEntry::make('period_to')
                                ->label('End Date')
                                ->date('d M Y'),
                        ]),
                    ]),

                InfoGrid::make(3)->schema([
                    InfoSection::make('Financial Overview')
                        ->columnSpan(2)
                        ->schema([
                            InfoGrid::make(3)->schema([
                                TextEntry::make('total_orders_amount')
                                    ->label('Total Order Sales')
                                    ->money('INR'),


                                TextEntry::make('total_vendor_earnings')
                                    ->label('Vendor Net Earnings')
                                    ->money('INR'),

                                TextEntry::make('adjustments')
                                    ->label('Adjustments')
                                    ->money('INR')
                                    ->helperText('Credits / Debits applied by admin'),

                                TextEntry::make('final_payout_amount')
                                    ->label('Final Payout Amount')
                                    ->money('INR')
                                    ->weight('bold'),
                            ]),
                        ]),

                    InfoSection::make('Settlement Details')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('paid_at')
                                ->label('Paid At')
                                ->dateTime('d M Y h:i A')
                                ->placeholder('—'),

                            TextEntry::make('payment_reference')
                                ->label('UTR / Bank UTR Reference')
                                ->placeholder('—')
                                ->copyable(),

                            TextEntry::make('notes')
                                ->label('Settlement Notes')
                                ->placeholder('—'),
                        ]),
                ]),

                InfoSection::make('Settled Bank Account')
                    ->schema([
                        InfoGrid::make(4)->schema([
                            TextEntry::make('bank_account_name')
                                ->label('Account Holder Name')
                                ->placeholder('—'),

                            TextEntry::make('bank_name')
                                ->label('Bank Name')
                                ->placeholder('—'),

                            TextEntry::make('bank_account_number')
                                ->label('Account Number')
                                ->placeholder('—')
                                ->state(fn ($record) => $record->bank_account_number ? '••••' . substr($record->bank_account_number, -4) : '—'),

                            TextEntry::make('bank_ifsc_code')
                                ->label('IFSC Code')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
            'view'  => ViewPayout::route('/{record}'),
        ];
    }
}
