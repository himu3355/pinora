<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // <--- Hides from sidebar
    
    protected static ?string $model = SubscriptionPlan::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Subscription Plan';

    protected static ?string $pluralModelLabel = 'Subscription Plans';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('₹')
                            ->minValue(0),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                Section::make('Stripe Identifiers (Auto-synced)')
                    ->description(config('stripe.secret_key') ? null : '⚠️ Stripe keys are not configured in your .env. Auto-sync will be skipped until STRIPE_SECRET_KEY is configured.')
                    ->schema([
                        TextInput::make('stripe_product_id')
                            ->label('Stripe Product ID')
                            ->disabled()
                            ->placeholder('Will be generated automatically on save'),

                        TextInput::make('stripe_price_id')
                            ->label('Stripe Price ID')
                            ->disabled()
                            ->placeholder('Will be generated automatically on save'),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('stripe_product_id')
                    ->label('Stripe Product')
                    ->placeholder('—'),

                TextColumn::make('stripe_price_id')
                    ->label('Stripe Price')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
