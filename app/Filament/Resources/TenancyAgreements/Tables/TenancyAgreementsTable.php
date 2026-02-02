<?php

namespace App\Filament\Resources\TenancyAgreements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenancyAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('property.name')
                    ->label('Property')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('agreed_rent')
                    ->money('MYR')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('expiring_soon')
                    ->label('Expiring Next Month')
                    ->query(fn (Builder $query) => $query->whereBetween('end_date', [now(), now()->addMonth()])),
            ])
            ->defaultSort('end_date', 'asc')
            ->actions([
                EditAction::make(),
                Action::make('renew')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->form([
                        DatePicker::make('new_start_date')
                            ->required()
                            ->default(fn ($record) => $record->end_date->addDay()),
                        DatePicker::make('new_end_date')
                            ->required()
                            ->default(fn ($record) => $record->end_date->addYear()),
                        TextInput::make('new_rent')
                            ->label('New Rent Amount')
                            ->required()
                            ->numeric()
                            ->default(fn ($record) => $record->agreed_rent),
                    ])
                    ->action(function ($record, array $data) {
                        // Deactivate old agreement
                        $record->update(['is_active' => false]);
                        
                        // Create new agreement
                        \App\Models\TenancyAgreement::create([
                            'tenant_id' => $record->tenant_id,
                            'property_id' => $record->property_id,
                            'start_date' => $data['new_start_date'],
                            'end_date' => $data['new_end_date'],
                            'agreed_rent' => $data['new_rent'],
                            'is_active' => true,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Tenancy Renewed Successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
