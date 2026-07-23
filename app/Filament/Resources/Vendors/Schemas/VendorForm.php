<?php

namespace App\Filament\Resources\Vendors\Schemas;

use App\Models\User;
use App\Models\Vendor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorForm
{
    public static function configure(Schema $schema): Schema
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

        return $schema->components([
            Section::make('Store Information')
                ->schema([
                    Select::make('user_id')
                        ->label('Vendor Owner (User Account)')
                        ->options(
                            User::orderBy('name')
                                ->get()
                                ->mapWithKeys(fn(User $user) => [$user->id => "{$user->name} ({$user->email})"])
                        )
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->unique(User::class, 'email')
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('password')
                                ->label('Password')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->dehydrateStateUsing(fn(string $state) => Hash::make($state)),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $user = User::create([
                                'name' => $data['name'],
                                'email' => $data['email'],
                                'phone' => $data['phone'] ?? null,
                                'password' => $data['password'],
                                'status' => 'active',
                            ]);

                            $user->assignRole('vendor');

                            return $user->id;
                        })
                        ->helperText('Select an existing user account or create a new user account for this vendor.'),

                    Grid::make(2)->schema([
                        TextInput::make('store_name')
                            ->label('Store Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) =>
                                $set('store_slug', Str::slug($state ?? ''))
                            ),

                        TextInput::make('store_slug')
                            ->label('Store Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Vendor::class, 'store_slug', ignoreRecord: true)
                            ->helperText('Auto-populated from store name. Must be unique.'),
                    ]),

                    Textarea::make('description')
                        ->label('Store Description')
                        ->rows(4)
                        ->maxLength(2000),

                    Grid::make(2)->schema([
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
                    ]),
                ]),

            Section::make('Contact Information')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(15),

                        TextInput::make('email')
                            ->label('Business Email')
                            ->email()
                            ->maxLength(255),
                    ]),

                    Textarea::make('address')
                        ->label('Street Address')
                        ->rows(2),

                    Grid::make(3)->schema([
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
                    ]),
                ]),

            Section::make('Business Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('gst_number')
                            ->label('GST Number')
                            ->maxLength(15)
                            ->placeholder('22AAAAA0000A1Z5'),

                        TextInput::make('pan_number')
                            ->label('PAN Number')
                            ->maxLength(10)
                            ->placeholder('ABCDE1234F'),
                    ]),
                ]),

            Section::make('Status')
                ->schema([
                    Select::make('status')
                        ->label('Vendor Status')
                        ->options([
                            'pending'   => 'Pending',
                            'approved'  => 'Approved',
                            'suspended' => 'Suspended',
                            'rejected'  => 'Rejected',
                        ])
                        ->default('pending')
                        ->required(),
                ]),

            Section::make('Bank Details')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->maxLength(255),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),

                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(20),

                        TextInput::make('bank_ifsc_code')
                            ->label('IFSC Code')
                            ->maxLength(11)
                            ->placeholder('SBIN0001234'),
                    ]),
                ]),
        ]);
    }
}
