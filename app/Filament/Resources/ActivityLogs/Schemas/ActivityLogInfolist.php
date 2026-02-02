<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Schemas\Schema;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Log Details')
                    ->schema([
                        TextEntry::make('log_name'),
                        TextEntry::make('event'),
                        TextEntry::make('description'),
                        TextEntry::make('subject_type'),
                        TextEntry::make('subject_id'),
                        TextEntry::make('causer.name')->label('User'),
                        TextEntry::make('created_at')->dateTime(),
                    ])->columns(2),
                Section::make('Changes')
                    ->schema([
                        KeyValueEntry::make('properties.attributes')
                            ->label('New Values')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                        KeyValueEntry::make('properties.old')
                            ->label('Old Values')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }
}
