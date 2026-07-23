<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('semibold'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->state(fn(Review $record) => $record->rating_stars)
                    ->color(fn(Review $record): string => match (true) {
                        $record->rating >= 4 => 'success',
                        $record->rating === 3 => 'warning',
                        default              => 'danger',
                    }),

                TextColumn::make('title')
                    ->label('Title')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? '')),

                IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip('Verified Purchase'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
                    ->orderBy('created_at', 'desc');
            })
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable(),

                SelectFilter::make('rating')
                    ->options([
                        '1' => '1 Star',
                        '2' => '2 Stars',
                        '3' => '3 Stars',
                        '4' => '4 Stars',
                        '5' => '5 Stars',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Submitted From'),
                        DatePicker::make('until')->label('Submitted Until'),
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

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Review')
                    ->modalDescription('This review will be published on the product page.')
                    ->visible(fn(Review $record) => $record->status !== 'approved')
                    ->action(function (Review $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Review approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Review $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('admin_note')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Why is this review being rejected?'),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->update([
                            'status'      => 'rejected',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'admin_note'  => $data['admin_note'],
                        ]);
                        Notification::make()
                            ->title('Review rejected')
                            ->danger()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Review $review): void {
                                if ($review->status !== 'approved') {
                                    $review->update([
                                        'status'      => 'approved',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                    ]);
                                }
                            });
                            Notification::make()
                                ->title(count($records) . ' reviews approved')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Textarea::make('admin_note')
                                ->label('Rejection Reason (applies to all selected)')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Review $review) use ($data): void {
                                if ($review->status !== 'rejected') {
                                    $review->update([
                                        'status'      => 'rejected',
                                        'approved_at' => now(),
                                        'approved_by' => auth()->id(),
                                        'admin_note'  => $data['admin_note'],
                                    ]);
                                }
                            });
                            Notification::make()
                                ->title(count($records) . ' reviews rejected')
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No reviews to moderate')
            ->emptyStateIcon('heroicon-o-star');
    }
}
