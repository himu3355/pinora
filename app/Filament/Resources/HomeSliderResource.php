<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSliderResource\Pages;
use App\Models\HomeSlider;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeSliderResource extends Resource
{
    protected static ?string $model = HomeSlider::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Home Sliders';

    protected static ?string $modelLabel = 'Home Slider';

    protected static ?string $pluralModelLabel = 'Home Sliders';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slide Content')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('badge_text')
                                ->label('Top Eyebrow / Brand')
                                ->default("RAJKOT'S")
                                ->maxLength(255),
                            TextInput::make('title')
                                ->label('Heading Title')
                                ->default('NOSE PIN SPECIALIST')
                                ->required()
                                ->maxLength(255),
                        ]),

                        TextInput::make('subtitle')
                            ->label('Subtitle / Tagline')
                            ->default('Elegant 22K Gold Nose Pins for Every Occasion.')
                            ->maxLength(255),

                        Grid::make(3)->schema([
                            TextInput::make('button_text')
                                ->label('CTA Button Text')
                                ->default('SHOP NOSE PINS'),
                            TextInput::make('button_url')
                                ->label('CTA Button Link')
                                ->default('/shop'),
                            TextInput::make('badge_pill_text')
                                ->label('Floating Badge Overlay')
                                ->default('22K BIS Hallmarked')
                                ->helperText('Displayed at bottom of banner image'),
                        ]),
                    ]),

                Section::make('Slide Images & Order')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('desktop_image')
                                ->label('Desktop Banner Image')
                                ->image()
                                ->disk('public')
                                ->directory('sliders')
                                ->helperText('Recommended: High-resolution portrait or hero banner image (approx 800x1000px).'),
                            FileUpload::make('mobile_image')
                                ->label('Mobile Banner Image (Optional)')
                                ->image()
                                ->disk('public')
                                ->directory('sliders')
                                ->helperText('Fallback uses desktop image if empty.'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('desktop_image')
                    ->label('Image')
                    ->size(50)
                    ->rounded(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->limit(40)
                    ->color('gray'),

                TextColumn::make('button_text')
                    ->label('CTA Button')
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
            'index' => Pages\ListHomeSliders::route('/'),
            'create' => Pages\CreateHomeSlider::route('/create'),
            'edit' => Pages\EditHomeSlider::route('/{record}/edit'),
        ];
    }
}
