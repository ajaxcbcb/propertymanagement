<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PropertyForm
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
                
                \Filament\Forms\Components\Textarea::make('address')
                    ->label('Full Address')
                    ->required()
                    ->rows(3),
                
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
