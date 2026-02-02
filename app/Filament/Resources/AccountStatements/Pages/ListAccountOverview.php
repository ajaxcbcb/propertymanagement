<?php

namespace App\Filament\Resources\AccountStatements\Pages;

use App\Filament\Resources\AccountStatements\AccountOverviewResource;
use Filament\Resources\Pages\ListRecords;

class ListAccountOverview extends ListRecords
{
    protected static string $resource = AccountOverviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No creation needed for overview
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        $tenantId = request()->query('tenant');
        $propertyId = request()->query('property');
        
        if ($tenantId || $propertyId) {
            $filters = $this->tableFilters ?? [];
            
            if ($tenantId) {
                // Support both single ID and array/comma-separated IDs
                $ids = is_array($tenantId) ? $tenantId : explode(',', $tenantId);
                $filters['tenant_id'] = ['values' => $ids];
            }
            
            if ($propertyId) {
                // Support both single ID and array/comma-separated IDs
                $ids = is_array($propertyId) ? $propertyId : explode(',', $propertyId);
                $filters['property_id'] = ['values' => $ids];
            }
            
            $this->tableFilters = $filters;
        }
    }
}
