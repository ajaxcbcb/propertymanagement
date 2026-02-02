<?php

namespace App\Observers;

use App\Models\Tenant;

class TenantObserver
{
    /**
     * Handle the Tenant "updating" event.
     * Enforce the "No Revert" rule for SST registration.
     */
    public function updating(Tenant $tenant): void
    {
        // Check if is_sst_registered is being changed
        if ($tenant->isDirty('is_sst_registered')) {
            $originalValue = $tenant->getOriginal('is_sst_registered');
            $newValue = $tenant->is_sst_registered;

            // If trying to change from true to false, throw an exception
            if ($originalValue === true && $newValue === false) {
                throw new \Exception('COMPLIANCE ERROR: You cannot revert an SST-registered customer.');
            }

            // If changing from false to true, automatically set the registration date
            if ($originalValue === false && $newValue === true) {
                $tenant->sst_registration_date = now();
            }
        }
    }

    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "restored" event.
     */
    public function restored(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "force deleted" event.
     */
    public function forceDeleted(Tenant $tenant): void
    {
        //
    }
}
