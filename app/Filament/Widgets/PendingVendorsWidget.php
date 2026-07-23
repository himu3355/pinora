<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Notifications\Notification;

class PendingVendorsWidget extends TableWidget
{
    protected static ?string $heading = 'Vendors Awaiting Approval';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vendor::query()
                    ->where('status', 'pending')
                    ->with('user')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('store_name')
                    ->label('Store Name')
                    ->weight('semibold')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->color('gray'),

                TextColumn::make('city')
                    ->label('City')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->since(),
            ])
            ->actions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Vendor $record) => VendorResource::getUrl('view', ['record' => $record])),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Vendor $record): void {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Vendor ' . $record->store_name . ' approved')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->paginated(false)
            ->emptyStateHeading('No pending vendors')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateDescription('All vendor applications have been reviewed.');
    }
}
