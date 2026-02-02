<?php

namespace App\Filament\Resources\Properties;

use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\EditProperty;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use App\Filament\Resources\Properties\Tables\PropertiesTable;
use App\Models\Property;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static string | \UnitEnum | null $navigationGroup = 'Property Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'address', 'lot_number'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        $details = [
            'Lot Number' => $record->lot_number,
            'Address' => $record->address,
            'Type' => ucfirst($record->type),
            'Status' => ucfirst($record->status),
        ];

        // Get active tenancy agreements
        $activeTenancies = $record->tenancyAgreements()
            ->where('is_active', true)
            ->with('tenant')
            ->get();

        if ($activeTenancies->isNotEmpty()) {
            $tenantNames = $activeTenancies->pluck('tenant.name')->filter()->implode(', ');
            if ($tenantNames) {
                $details['Current Tenants'] = $tenantNames;
            }
        }

        return $details;
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name . ' (' . $record->lot_number . ')';
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return route('filament.admin.resources.account-statements.account-overviews.index', [
            'property' => $record->id,
        ]);
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['tenancyAgreements.tenant']);
    }

    public static function form(Schema $schema): Schema
    {
        return PropertyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProperties::route('/'),
            'create' => CreateProperty::route('/create'),
            'edit' => EditProperty::route('/{record}/edit'),
        ];
    }
}
