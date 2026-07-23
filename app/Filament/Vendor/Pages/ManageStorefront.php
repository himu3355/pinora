<?php

namespace App\Filament\Vendor\Pages;

use App\Models\Vendor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Redberry\PageBuilderPlugin\Components\Forms\PageBuilder;

class ManageStorefront extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'My Storefront';
    protected static ?string $title = 'Manage Storefront';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.vendor.pages.manage-storefront';

    public ?array $data = [];
    public ?Vendor $record = null;

    public function mount(): void
    {
        $this->record = auth()->user()->vendor;
        if (! $this->record) {
            abort(403, 'Vendor profile not found.');
        }

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        $indianStates = [
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CG' => 'Chhattisgarh',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OD' => 'Odisha',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TS' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UK' => 'Uttarakhand',
            'WB' => 'West Bengal',
            'AN' => 'Andaman and Nicobar Islands',
            'CH' => 'Chandigarh',
            'DN' => 'Dadra and Nagar Haveli and Daman and Diu',
            'DL' => 'Delhi',
            'JK' => 'Jammu and Kashmir',
            'LA' => 'Ladakh',
            'LD' => 'Lakshadweep',
            'PY' => 'Puducherry',
        ];

        return $schema
            ->components([
                TextInput::make('store_name')
                    ->label('Store Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Store Description')
                    ->rows(4)
                    ->maxLength(2000),

                FileUpload::make('logo')
                    ->label('Store Logo')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/logos')
                    ->maxSize(2048),

                FileUpload::make('banner')
                    ->label('Store Banner')
                    ->image()
                    ->disk('public')
                    ->directory('vendors/banners')
                    ->maxSize(5120),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(15),

                TextInput::make('email')
                    ->label('Business Email')
                    ->email()
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Street Address')
                    ->rows(2),

                TextInput::make('city')
                    ->label('City')
                    ->maxLength(100),

                Select::make('state')
                    ->label('State')
                    ->options($indianStates)
                    ->searchable(),

                TextInput::make('pincode')
                    ->label('Pincode')
                    ->maxLength(6)
                    ->numeric(),

                PageBuilder::make('pageBuilderBlocks')
                    ->label('Custom Homepage Blocks')
                    ->blocks([
                        \App\Filament\Blocks\Hero::class,
                        \App\Filament\Blocks\VermiCompost::class,
                    ])
                    ->reorderable()
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview')
                ->label('Preview Storefront')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('vendors.show', $this->record->store_slug), shouldOpenInNewTab: true)
                ->color('gray'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title('Storefront Updated')
            ->body('Your storefront settings and homepage blocks have been updated successfully.')
            ->success()
            ->send();
    }
}
