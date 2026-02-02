<?php

namespace App\Filament\Resources\TenancyAgreements;

use App\Filament\Resources\TenancyAgreements\Pages\CreateTenancyAgreement;
use App\Filament\Resources\TenancyAgreements\Pages\EditTenancyAgreement;
use App\Filament\Resources\TenancyAgreements\Pages\ListTenancyAgreements;
use App\Filament\Resources\TenancyAgreements\Schemas\TenancyAgreementForm;
use App\Filament\Resources\TenancyAgreements\Tables\TenancyAgreementsTable;
use App\Models\TenancyAgreement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TenancyAgreementResource extends Resource
{
    protected static ?string $model = TenancyAgreement::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | \UnitEnum | null $navigationGroup = 'Property Management';

    public static function getGloballySearchableAttributes(): array
    {
        return ['tenant.name', 'property.name', 'property.address', 'property.lot_number'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        $details = [];

        if ($record->tenant) {
            $details['Tenant'] = $record->tenant->name;
        }

        if ($record->property) {
            $details['Property'] = $record->property->name . ' (' . $record->property->lot_number . ')';
        }

        $details['Rent'] = 'RM ' . number_format($record->agreed_rent, 2);
        $details['Period'] = $record->start_date->format('M Y') . ' - ' . $record->end_date->format('M Y');
        $details['Status'] = $record->is_active ? 'Active' : 'Inactive';

        return $details;
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        $tenantName = $record->tenant?->name ?? 'Unknown Tenant';
        $propertyName = $record->property?->name ?? 'Unknown Property';
        return "Agreement: {$tenantName} @ {$propertyName}";
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['tenant', 'property']);
    }

    public static function form(Schema $schema): Schema
    {
        return TenancyAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenancyAgreementsTable::configure($table);
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
            'index' => ListTenancyAgreements::route('/'),
            'create' => CreateTenancyAgreement::route('/create'),
            'edit' => EditTenancyAgreement::route('/{record}/edit'),
        ];
    }
}
