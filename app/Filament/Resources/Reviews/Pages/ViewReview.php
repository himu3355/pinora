<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class ViewReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    public function getTitle(): string
    {
        return 'Review: ' . ($this->record->title ?: 'No Title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve Review')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status !== 'approved')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);
                    $this->refreshFormData(['status', 'approved_at']);
                    \Filament\Notifications\Notification::make()
                        ->title('Review approved')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject Review')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->status !== 'rejected')
                ->form([
                    Textarea::make('admin_note')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'      => 'rejected',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'admin_note'  => $data['admin_note'],
                    ]);
                    $this->refreshFormData(['status', 'admin_note']);
                    \Filament\Notifications\Notification::make()
                        ->title('Review rejected')
                        ->danger()
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
