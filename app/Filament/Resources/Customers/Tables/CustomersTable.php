<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\User;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->disk('public'),

                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('customer_tag')
                    ->label('Tag')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'regular' => 'gray',
                        'vip' => 'warning',
                        'premium' => 'success',
                        'new' => 'info',
                        'at_risk' => 'danger',
                        'churned' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->state(fn(User $record) =>
                        $record->orders()->where('payment_status', 'paid')->sum('total_amount')
                    )
                    ->money('INR')
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),

                SelectFilter::make('customer_tag')
                    ->label('Tag')
                    ->options([
                        'regular'  => 'Regular',
                        'vip'      => 'VIP',
                        'premium'  => 'Premium',
                        'new'      => 'New',
                        'at_risk'  => 'At Risk',
                        'churned'  => 'Churned',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active'    => 'Active',
                        'suspended' => 'Suspended',
                    ]),

                Filter::make('joined')
                    ->form([
                        DatePicker::make('from')->label('Joined From'),
                        DatePicker::make('until')->label('Joined Until'),
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
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
                \Filament\Actions\ForceDeleteAction::make()
                    ->action(function (User $record): void {
                        try {
                            $record->forceDelete();
                            Notification::make()->title('Customer permanently deleted')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Cannot delete customer')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('change_tag')
                    ->label('Change Tag')
                    ->icon('heroicon-o-tag')
                    ->color('gray')
                    ->form([
                        Select::make('customer_tag')
                            ->label('New Tag')
                            ->options([
                                'regular'  => 'Regular',
                                'vip'      => 'VIP',
                                'premium'  => 'Premium',
                                'new'      => 'New',
                                'at_risk'  => 'At Risk',
                                'churned'  => 'Churned',
                            ])
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update(['customer_tag' => $data['customer_tag']]);
                        Notification::make()
                            ->title('Tag updated')
                            ->success()
                            ->send();
                    }),

                Action::make('toggle_status')
                    ->label(fn(User $record) => $record->status === 'active' ? 'Suspend' : 'Activate')
                    ->icon(fn(User $record) => $record->status === 'active' ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn(User $record) => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $newStatus = $record->status === 'active' ? 'suspended' : 'active';
                        $record->update(['status' => $newStatus]);
                        Notification::make()
                            ->title('Status updated to ' . $newStatus)
                            ->success()
                            ->send();
                    }),

                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Impersonate Customer')
                    ->modalDescription(fn(User $record) => "You will be logged in as {$record->name} on the storefront. A banner will allow you to exit.")
                    ->action(function (User $record) {
                        // Store the real admin's ID so we can restore them later
                        session()->put('impersonating_admin_id', auth()->id());
                        // Switch auth to the customer
                        auth()->login($record);
                        return redirect('/');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    BulkAction::make('bulk_tag')
                        ->label('Tag Selected')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('customer_tag')
                                ->label('Tag')
                                ->options([
                                    'regular'  => 'Regular',
                                    'vip'      => 'VIP',
                                    'premium'  => 'Premium',
                                    'at_risk'  => 'At Risk',
                                    'churned'  => 'Churned',
                                ])
                                ->required(),
                        ])
                        ->action(fn(Collection $records, array $data) =>
                            $records->each(fn(User $u) => $u->update(['customer_tag' => $data['customer_tag']]))
                        ),
                ]),
            ])
            ->emptyStateHeading('No customers found')
            ->emptyStateIcon('heroicon-o-users');
    }
}
