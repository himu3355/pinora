<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Review Details')
                ->icon('heroicon-o-star')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('product_name')
                            ->label('Product')
                            ->formatStateUsing(fn(?Review $record) => $record?->product?->name)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('customer_name')
                            ->label('Customer')
                            ->formatStateUsing(fn(?Review $record) => $record?->user?->name)
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('rating')
                            ->label('Rating')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('/ 5'),

                        TextInput::make('title')
                            ->label('Review Title')
                            ->maxLength(255),
                    ]),

                    Textarea::make('body')
                        ->label('Review Body')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        TextInput::make('admin_note')
                            ->label('Admin Note')
                            ->maxLength(500)
                            ->helperText('Reason for rejection (if applicable).'),
                    ]),
                ]),
        ]);
    }
}
