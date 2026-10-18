<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeValuePropResource\Pages;
use App\Models\HomeValueProp;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeValuePropResource extends Resource
{
    protected static ?string $model = HomeValueProp::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Trust & Value Badges';

    protected static ?string $modelLabel = 'Trust Badge';

    protected static ?string $pluralModelLabel = 'Trust Badges';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Badge Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Title (e.g. 22K Gold)')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('subtitle')
                                ->label('Gujarati / Regional Subtitle (e.g. શુદ્ધ સોનાની ખાતરી)')
                                ->maxLength(255),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('icon')
                                ->label('Icon Style')
                                ->options([
                                    'gold-bar' => 'Gold Bar / Ingot',
                                    'hallmark' => 'BIS Hallmark Triangle',
                                    'craftsman' => 'Master Craftsmanship',
                                    'shipping' => 'Insured Shipping',
                                    'exchange' => 'Easy 7-Day Exchange',
                                    'lifetime' => '100% Lifetime Exchange',
                                    'sparkles' => 'Sparkles / Solitaire',
                                ])
                                ->default('sparkles')
                                ->required(),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),

                        Textarea::make('description')
                            ->label('Optional Description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('subtitle')
                    ->label('Gujarati Subtitle')
                    ->color('gray'),

                TextColumn::make('icon')
                    ->label('Icon')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListHomeValueProps::route('/'),
            'create' => Pages\CreateHomeValueProp::route('/create'),
            'edit' => Pages\EditHomeValueProp::route('/{record}/edit'),
        ];
    }
}
