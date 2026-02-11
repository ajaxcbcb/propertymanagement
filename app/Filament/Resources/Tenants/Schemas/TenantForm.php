<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('wallet_balance')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('RM')
                            ->minValue(0),
                    ])
                    ->columns(2),

                Section::make('Tax Compliance')
                    ->description('Warning: Once SST registration is enabled, it cannot be disabled.')
                    ->schema([
                        Toggle::make('is_sst_registered')
                            ->label('SST Registered')
                            ->helperText('Warning: Once enabled, this cannot be disabled.')
                            ->disabled(fn($record) => $record?->is_sst_registered === true)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('sst_registration_date', now());
                                }
                            }),
                        DatePicker::make('sst_registration_date')
                            ->label('SST Registration Date')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn($get) => $get('is_sst_registered')),
                    ])
                    ->columns(2),
            ]);
    }
}
