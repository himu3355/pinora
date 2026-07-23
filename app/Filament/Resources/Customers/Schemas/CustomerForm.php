<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignoreRecord: true),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                                'other'  => 'Other / Prefer not to say',
                            ])
                            ->nullable(),
                    ]),

                    Grid::make(2)->schema([
                        DatePicker::make('birthday')
                            ->label('Date of Birth')
                            ->maxDate(today()->subYears(13)->toDateString()),

                        DatePicker::make('anniversary_date')
                            ->label('Anniversary Date'),
                    ]),

                    FileUpload::make('avatar')
                        ->label('Profile Picture')
                        ->image()
                        ->disk('public')
                        ->directory('users/avatars')
                        ->maxSize(2048)
                        ->circular(),
                ]),

            Section::make('Jewellery Preferences')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('ring_size')
                            ->label('Ring Size')
                            ->options(array_combine(
                                range(1, 30),
                                array_map(fn($s) => "Size $s", range(1, 30))
                            ))
                            ->nullable()
                            ->searchable(),

                        Select::make('bangle_size')
                            ->label('Bangle Size')
                            ->options([
                                '2/2'  => '2/2 (XS)',
                                '2/4'  => '2/4 (S)',
                                '2/6'  => '2/6 (M)',
                                '2/8'  => '2/8 (L)',
                                '2/10' => '2/10 (XL)',
                                '2/12' => '2/12 (XXL)',
                            ])
                            ->nullable(),
                    ]),
                ]),

            Section::make('Account Management')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('customer_tag')
                            ->label('Customer Tag')
                            ->options([
                                'regular'  => 'Regular',
                                'vip'      => 'VIP',
                                'premium'  => 'Premium',
                                'new'      => 'New',
                                'at_risk'  => 'At Risk',
                                'churned'  => 'Churned',
                            ])
                            ->default('regular')
                            ->helperText('Tags help segment customers for marketing.'),

                        Select::make('status')
                            ->label('Account Status')
                            ->options([
                                'active'    => 'Active',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                ]),
        ]);
    }
}
