<?php

namespace App\Observers;

use App\Models\TenancyAgreement;

class TenancyAgreementObserver
{
    /**
     * Handle the TenancyAgreement "saving" event.
     */
    public function saving(TenancyAgreement $tenancyAgreement): void
    {
        // Auto-expire if end_date is in the past
        if ($tenancyAgreement->end_date < now()->startOfDay() && $tenancyAgreement->is_active) {
            $tenancyAgreement->is_active = false;
        }
    }
}
