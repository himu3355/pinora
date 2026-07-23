<?php

namespace App\Filament\Resources\MetalRates\Schemas;

use App\Models\MetalRate;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MetalRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Metal Rate Entry')
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
                            ->afterStateUpdated(fn(Set $set) => $set('purity', null)),

                        Select::make('purity')
                            ->label('Purity')
                            ->options(function (Get $get): array {
                                return match ($get('metal_type')) {
                                    'gold'     => ['24K' => '24K', '22K' => '22K', '18K' => '18K', '14K' => '14K'],
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
                        ->placeholder('Optional — e.g., Source: IBJA, or market comment'),
                ]),
        ]);
    }
}
