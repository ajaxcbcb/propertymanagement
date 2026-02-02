<?php

namespace App\Filament\Resources\TenancyAgreements\Schemas;

use App\Models\TenancyAgreement;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenancyAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        $updateActiveStatus = function (Get $get, callable $set) {
            $startDate = $get('start_date');
            $endDate = $get('end_date');

            if ($startDate && $endDate) {
                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);
                $set('is_active', now()->betweenIncluded($start, $end));
            }
        };

        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('property_id')
                    ->relationship('property', 'name', modifyQueryUsing: function (Builder $query) {
                        return $query->where('status', 'vacant');
                    })
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} (Lot: {$record->lot_number}) - RM {$record->base_rent}")
                    ->searchable(['name', 'lot_number', 'address'])
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $property = \App\Models\Property::find($state);
                            if ($property) {
                                $set('agreed_rent', $property->base_rent);
                            }
                        }
                    })
                    ->rules(fn (Get $get, ?Model $record): array => [
                        function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if ($get('is_active')) {
                                $isActive = \App\Models\TenancyAgreement::where('property_id', $value)
                                    ->where('is_active', true)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->exists();

                                if ($isActive) {
                                    $fail('This property already has an active tenancy agreement.');
                                }
                            }
                        },
                    ]),
                DatePicker::make('start_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated($updateActiveStatus),
                DatePicker::make('end_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated($updateActiveStatus),
                TextInput::make('agreed_rent')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
            ]);
    }
}
