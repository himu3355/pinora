<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->size(40)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('— Top Level —')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Filter::make('is_active')
                    ->label('Active Only')
                    ->query(fn(Builder $query) => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('top_level')
                    ->label('Top Level Only')
                    ->query(fn(Builder $query) => $query->whereNull('parent_id'))
                    ->toggle(),

                SelectFilter::make('parent_id')
                    ->label('Under Parent')
                    ->options(
                        Category::whereNull('parent_id')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->placeholder('All'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn(Category $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn(Category $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Category $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn(Category $record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => true]))
                        ),

                    BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Category $c) => $c->update(['is_active' => false]))
                        ),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
