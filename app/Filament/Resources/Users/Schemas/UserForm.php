<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\Users\Pages\CreateUser;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => $query->whereNotIn('name', ['vendor', 'vendor_staff']))
                    ->multiple()
                    ->preload(),
            ]);
    }
}
