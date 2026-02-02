<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        // Check for tenant filter from global search
        $tenantId = request()->query('tenant');
        
        if ($tenantId) {
            $this->tableFilters = [
                'tenant_id' => ['value' => $tenantId],
            ];
        }
    }
}
