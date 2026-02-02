<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PropertyFormFree
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Property Name')
                    ->required(),
                TextInput::make('lot_number')
                    ->label('Lot Number')
                    ->placeholder('e.g. Lot 123, Level 2'),
                
                // FREE OpenStreetMap Autocomplete for Address (No API Key Required!)
                \App\Forms\Components\OpenStreetMapAutocomplete::make('address')
                    ->label('Full Address')
                    ->placeholder('Start typing to search address... (min 3 characters)')
                    ->required()
                    ->latitudeField('latitude')
                    ->longitudeField('longitude')
                    ->cityField('city')
                    ->stateField('state')
                    ->postalCodeField('postal_code')
                    ->countryField('country')
                    ->helperText('🆓 Free address search - No API key needed!'),
                
                // Hidden fields for location data (auto-populated)
                \Filament\Forms\Components\Hidden::make('latitude'),
                \Filament\Forms\Components\Hidden::make('longitude'),
                
                // Display fields for location data (read-only)
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('city')
                            ->label('City')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('state')
                            ->label('State')
                            ->disabled()
                            ->dehydrated(),
                    ]),
                
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('country')
                            ->label('Country')
                            ->disabled()
                            ->dehydrated()
                            ->default('Malaysia'),
                    ]),
                
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'Apartment' => 'Apartment',
                        'House' => 'House',
                        'Commercial' => 'Commercial',
                        'Office' => 'Office',
                        'Retail' => 'Retail',
                    ])
                    ->required(),
                TextInput::make('base_rent')
                    ->label('Base Rent (RM)')
                    ->required()
                    ->numeric()
                    ->prefix('RM'),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'vacant' => 'Vacant',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Under Maintenance',
                    ])
                    ->required()
                    ->default('vacant'),
            ]);
    }
}
