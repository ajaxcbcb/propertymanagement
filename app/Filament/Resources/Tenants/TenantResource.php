<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Tenants\RelationManagers\TenancyAgreementsRelationManager;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | \UnitEnum | null $navigationGroup = 'Property Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        $details = [
            'Email' => $record->email ?? 'N/A',
            'Phone' => $record->phone ?? 'N/A',
            'SST Registered' => $record->is_sst_registered ? 'Yes' : 'No',
        ];

        // Get all properties through tenancy agreements
        $properties = $record->tenancyAgreements()
            ->with('property')
            ->get()
            ->pluck('property')
            ->filter()
            ->unique('id');

        if ($properties->isNotEmpty()) {
            $propertyNames = $properties->pluck('name')->implode(', ');
            $details['Properties'] = $propertyNames;
        }

        // Get active tenancies count
        $activeTenancies = $record->tenancyAgreements()->where('is_active', true)->count();
        if ($activeTenancies > 0) {
            $details['Active Tenancies'] = $activeTenancies;
        }

        // Get invoice statistics
        $totalInvoices = $record->invoices()->count();
        $pendingInvoices = $record->invoices()->where('status', 'pending')->count();

        if ($totalInvoices > 0) {
            $invoiceStats = [];
            if ($pendingInvoices > 0) {
                $invoiceStats[] = "$pendingInvoices pending";
            }
            if (!empty($invoiceStats)) {
                $details['Invoices'] = implode(', ', $invoiceStats);
            }
        }

        return $details;
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return route('filament.admin.resources.account-statements.account-overviews.index', [
            'tenant' => $record->id,
        ]);
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['tenancyAgreements.property', 'invoices']);
    }

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }



    public static function getRelations(): array
    {
        return [
            TenancyAgreementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
