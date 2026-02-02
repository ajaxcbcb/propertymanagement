<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static string | \UnitEnum | null $navigationGroup = 'Finance';

    public static function getNavigationLabel(): string
    {
        return 'Payments';
    }

    public static function getModelLabel(): string
    {
        return 'Payment';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Payments';
    }

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'description'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        $details = [
            'Type' => ucfirst($record->type),
            'Amount' => 'RM ' . number_format($record->amount_total, 2),
            'Status' => ucfirst($record->status),
        ];

        if ($record->tenant) {
            $details['Tenant'] = $record->tenant->name;
        }

        if ($record->property) {
            $details['Property'] = $record->property->name;
        }

        return $details;
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->invoice_number ?? 'Invoice #' . $record->id;
    }
    
    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): ?string
    {
        // Redirect to Payments list filtered by tenant
        if ($record->tenant_id) {
            return static::getUrl('index', [
                'tenant' => $record->tenant_id,
            ]);
        }
        
        // If no tenant (e.g., expenditure), redirect to payments list
        return static::getUrl('index');
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // For global search, show all invoices regardless of status
        return static::getModel()::query()->with(['tenant', 'property', 'tenancyAgreement']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery();
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
