<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorSubscriptionResource\Pages;
use App\Models\VendorSubscription;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorSubscriptionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // <--- Hides from sidebar
    
    protected static ?string $model = VendorSubscription::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Vendor Subscription';

    protected static ?string $pluralModelLabel = 'Vendor Subscriptions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription Information')
                    ->schema([
                        Select::make('vendor_id')
                            ->relationship('vendor', 'store_name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->label('Vendor / Store'),

                        Select::make('status')
                            ->options([
                                'trialing' => 'Trialing',
                                'active' => 'Active',
                                'past_due' => 'Past Due',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('active')
                            ->label('Status'),

                        DateTimePicker::make('trial_ends_at')
                            ->label('Trial Ends At'),

                        DateTimePicker::make('ends_at')
                            ->label('Subscription Ends At'),
                    ])->columns(2),

                Section::make('Stripe Identifiers (Optional for manual management)')
                    ->schema([
                        TextInput::make('stripe_subscription_id')
                            ->label('Stripe Subscription ID')
                            ->maxLength(255),

                        TextInput::make('stripe_customer_id')
                            ->label('Stripe Customer ID')
                            ->maxLength(255),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vendor.store_name')
                    ->label('Store')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trialing' => 'amber',
                        'active' => 'success',
                        'past_due' => 'danger',
                        'cancelled' => 'gray',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? ''))
                    ->sortable(),

                TextColumn::make('trial_ends_at')
                    ->label('Trial Ends At')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('ends_at')
                    ->label('Ends At')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('stripe_subscription_id')
                    ->label('Stripe Sub ID')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorSubscriptions::route('/'),
            'create' => Pages\CreateVendorSubscription::route('/create'),
            'edit' => Pages\EditVendorSubscription::route('/{record}/edit'),
        ];
    }
}
