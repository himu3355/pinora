<?php

namespace App\Filament\Resources\MetalRates\Tables;

use App\Models\MetalRate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class MetalRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('metal_type')
                    ->label('Metal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gold'     => 'warning',
                        'silver'   => 'gray',
                        'platinum' => 'info',
                        default    => 'primary',
                    })
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
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
