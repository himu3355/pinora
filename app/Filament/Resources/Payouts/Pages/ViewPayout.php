<?php

namespace App\Filament\Resources\Payouts\Pages;

use App\Filament\Resources\Payouts\PayoutResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class ViewPayout extends EditRecord
{
    protected static string $resource = PayoutResource::class;

    public function getTitle(): string
    {
        return 'Payout: ' . $this->record->payout_reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_paid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn() => $this->record->status !== 'paid')
                ->form([
                    TextInput::make('payment_reference')
                        ->label('Payment Reference')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('paid_at')
                        ->label('Payment Date')
                        ->required()
                        ->default(today()->toDateString()),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'            => 'paid',
                        'payment_reference' => $data['payment_reference'],
                        'paid_at'           => $data['paid_at'],
                        'processed_by'      => auth()->id(),
                    ]);

                    $this->refreshFormData(['status', 'paid_at', 'payment_reference']);

                    \Filament\Notifications\Notification::make()
                        ->title('Payout marked as paid')
                        ->success()
                        ->send();
                }),

            Action::make('save')
                ->label('Save Changes')
                ->action(fn() => $this->save()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
