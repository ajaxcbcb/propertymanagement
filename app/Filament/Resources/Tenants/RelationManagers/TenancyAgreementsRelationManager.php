<?php

namespace App\Filament\Resources\Tenants\RelationManagers;

use App\Models\TenancyAgreement;
use Closure;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TenancyAgreementsRelationManager extends RelationManager
{
    protected static string $relationship = 'tenancyAgreements';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules([
                        fn (Get $get, ?Model $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if ($get('is_active')) {
                                $existingAgreement = TenancyAgreement::where('property_id', $value)
                                    ->where('is_active', true)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->first();

                                if ($existingAgreement) {
                                    $fail('This property already has an active tenancy agreement.');
                                }
                            }
                        },
                    ]),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('agreed_rent')
                    ->required()
                    ->numeric()
                    ->prefix('RM'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
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
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
