<?php

namespace App\Filament\Vendor\Resources\OrderItemResource\Pages;

use App\Filament\Vendor\Resources\OrderItemResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderItem extends ViewRecord
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Select::make('fulfillment_status')
                        ->label('Status')
                        ->options([
                            'pending'    => 'Pending',
                            'accepted'   => 'Accepted',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'delivered'  => 'Delivered',
                            'cancelled'  => 'Cancelled',
                        ])
                        ->required()
                        ->default(fn (): string => $this->record->fulfillment_status),
                ])
                ->action(function (array $data): void {
                    $updateData = ['fulfillment_status' => $data['fulfillment_status']];

                    if ($data['fulfillment_status'] === 'shipped') {
                        $updateData['shipped_at'] = now();
                    } elseif ($data['fulfillment_status'] === 'delivered') {
                        $updateData['delivered_at'] = now();
                    }

                    $this->record->update($updateData);
                }),

            Action::make('addTracking')
                ->label('Add Tracking')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->form([
                    TextInput::make('courier_name')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): ?string => $this->record->courier_name),
                    TextInput::make('tracking_number')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): ?string => $this->record->tracking_number),
                    TextInput::make('tracking_url')
                        ->url()
                        ->maxLength(500)
                        ->default(fn (): ?string => $this->record->tracking_url),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'courier_name'       => $data['courier_name'],
                        'tracking_number'    => $data['tracking_number'],
                        'tracking_url'       => $data['tracking_url'],
                        'fulfillment_status' => 'shipped',
                        'shipped_at'         => $this->record->shipped_at ?? now(),
                    ]);
                }),
        ];
    }
}
