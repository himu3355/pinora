<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Customer Profile')
                ->schema([
                    Grid::make(3)->schema([
                        ImageEntry::make('avatar')
                            ->label('Avatar')
                            ->circular()
                            ->disk('public'),

                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('phone')->label('Phone')->placeholder('—'),
                        TextEntry::make('gender')->label('Gender')->badge()->placeholder('—'),
                        TextEntry::make('birthday')->label('Birthday')->date('d M Y')->placeholder('—'),
                        TextEntry::make('anniversary_date')->label('Anniversary')->date('d M Y')->placeholder('—'),
                        TextEntry::make('ring_size')->label('Ring Size')->placeholder('—'),
                        TextEntry::make('bangle_size')->label('Bangle Size')->placeholder('—'),
                        TextEntry::make('customer_tag')->label('Tag')->badge(),
                        TextEntry::make('status')->label('Status')->badge()
                            ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                        TextEntry::make('created_at')->label('Joined')->date('d M Y'),
                    ]),
                ]),
        ]);
    }
}
