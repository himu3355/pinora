<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->state(fn(Product $record) => $record->primaryImage?->path ?? $record->images->first()?->path)
                    ->disk('public')
                    ->size(50)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(40),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('metal_type')
                    ->label('Metal')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'gold' => 'warning',
                        'silver' => 'gray',
                        'platinum' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                TextColumn::make('purity')
                    ->label('Purity')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('calculated_price')
                    ->label('Price (₹)')
                    ->state(fn(Product $record) => $record->calculated_price ?? $record->base_price)
                    ->money('INR')
                    ->sortable(false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter(),

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
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                    ]),

                SelectFilter::make('created_by')
                    ->label('Created By Admin')
                    ->options(
                        \App\Models\User::orderBy('name')->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(
                        Category::where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('metal_type')
                    ->options([
                        'gold'     => 'Gold',
                        'silver'   => 'Silver',
                        'platinum' => 'Platinum',
                        'other'    => 'Other',
                    ]),

                TernaryFilter::make('is_featured')
                    ->label('Featured'),

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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('toggle_featured')
                    ->label(fn(Product $record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color(fn(Product $record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn(Product $record) => $record->update(['is_featured' => !$record->is_featured])),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish_selected')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Product $p) => $p->update(['status' => 'active']))
                        ),

                    BulkAction::make('archive_selected')
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) =>
                            $records->each(fn(Product $p) => $p->update(['status' => 'inactive']))
                        ),
                ]),
            ])
            ->emptyStateHeading('No products found')
            ->emptyStateIcon('heroicon-o-squares-2x2');
    }
}
