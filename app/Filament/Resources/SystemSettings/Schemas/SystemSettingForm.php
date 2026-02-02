<?php

namespace App\Filament\Resources\SystemSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SystemSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->disabled()
                    ->required(),
                TextInput::make('label')
                    ->disabled()
                    ->required(),
                TextInput::make('value')
                    ->label('Configuration Value')
                    ->helperText(fn ($record) => $record?->description)
                    ->hidden(fn ($record) => in_array($record?->type, ['boolean', 'select', 'password']))
                    ->suffix(fn ($record) => match($record?->type) {
                        'percent' => '%',
                        'number' => '',
                        default => null,
                    })
                    ->required(),

                \Filament\Forms\Components\Toggle::make('value')
                    ->label('Enabled')
                    ->helperText(fn ($record) => $record?->description)
                    ->visible(fn ($record) => $record?->type === 'boolean')
                    ->required(),

                \Filament\Forms\Components\TextInput::make('value')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->helperText(fn ($record) => $record?->description)
                    ->visible(fn ($record) => $record?->type === 'password')
                    ->required(),

                TextInput::make('type')
                    ->disabled()
                    ->required()
                    ->default('string'),
                Textarea::make('description')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
