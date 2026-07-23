<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('store_name')
                    ->label('Store Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('store_slug')
                    ->label('Slug')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'approved'  => 'success',
                        'suspended' => 'danger',
                        'rejected'  => 'gray',
                    }),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),

                TextColumn::make('total_sales')
                    ->label('Total Sales')
                    ->money('INR')
                    ->state(fn(Vendor $record) => $record->orderItems()->where('fulfillment_status', 'delivered')->sum('total_price'))
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),

                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'suspended' => 'Suspended',
                        'rejected'  => 'Rejected',
                    ]),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Registered From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Registered Until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['created_from'],
                                fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date)
                            )
                            ->when($data['created_until'],
                                fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
                \Filament\Actions\ForceDeleteAction::make()
                    ->action(function (Vendor $record): void {
                        try {
                            $record->forceDelete();
                            Notification::make()->title('Vendor store permanently deleted')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Cannot delete vendor')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Vendor')
                    ->modalDescription('Are you sure you want to approve this vendor? They will gain access to the vendor panel.')
                    ->visible(fn(Vendor $record) => $record->status !== 'approved')
                    ->action(function (Vendor $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Vendor approved successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Vendor')
                    ->modalDescription('This vendor will lose access to the vendor panel.')
                    ->visible(fn(Vendor $record) => $record->status === 'approved')
                    ->action(function (Vendor $record): void {
                        $record->update(['status' => 'suspended']);
                        Notification::make()
                            ->title('Vendor suspended')
                            ->warning()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn(Vendor $record) => !in_array($record->status, ['rejected']))
                    ->action(function (Vendor $record, array $data): void {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()
                            ->title('Vendor rejected')
                            ->danger()
                            ->send();
                    }),

                Action::make('manage_as_vendor')
                    ->label('Manage as Vendor')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->visible(fn(Vendor $record) => $record->status === 'approved')
                    ->action(function (Vendor $record) {
                        session()->put('admin_managing_vendor_id', $record->id);
                        return redirect()->route('filament.vendor.pages.dashboard');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn(Vendor $vendor) => $vendor->update([
                                'status'      => 'approved',
                                'approved_at' => now(),
                            ]));
                            Notification::make()
                                ->title(count($records) . ' vendors approved')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('suspend_selected')
                        ->label('Suspend Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn(Vendor $vendor) => $vendor->update(['status' => 'suspended']));
                            Notification::make()
                                ->title(count($records) . ' vendors suspended')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }
}
