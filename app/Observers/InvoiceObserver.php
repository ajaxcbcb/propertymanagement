<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\PaymentOffsetService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    protected PaymentOffsetService $offsetService;

    public function __construct(PaymentOffsetService $offsetService)
    {
        $this->offsetService = $offsetService;
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Only process if this is a received payment with actual payment amount
        if ($invoice->type === 'received' && 
            $invoice->status === 'paid' && 
            $invoice->amount_received > 0 && 
            $invoice->tenant_id) {
            
            // Set original amount if not set
            if (!$invoice->original_amount_total) {
                $invoice->original_amount_total = $invoice->amount_total;
                $invoice->saveQuietly(); // Save without triggering events
            }

            // Apply payment offset logic
            try {
                $result = $this->offsetService->applyPaymentOffset($invoice);
                
                if ($result['success'] && !empty($result['offsets'])) {
                    Log::info("Payment offset triggered for invoice {$invoice->invoice_number}", $result);
                }
            } catch (\Exception $e) {
                Log::error("Failed to apply payment offset for invoice {$invoice->invoice_number}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Invoice "creating" event.
     */
    public function creating(Invoice $invoice): void
    {
        // Set original_amount_total when creating a new invoice
        if (!$invoice->original_amount_total && $invoice->amount_total) {
            $invoice->original_amount_total = $invoice->amount_total;
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // If status changed to 'paid' and there's a payment amount, trigger offset
        if ($invoice->wasChanged('status') && 
            $invoice->status === 'paid' && 
            $invoice->type === 'received' &&
            $invoice->amount_received > 0 && 
            $invoice->tenant_id) {
            
            try {
                $result = $this->offsetService->applyPaymentOffset($invoice);
                
                if ($result['success'] && !empty($result['offsets'])) {
                    Log::info("Payment offset triggered on status update for invoice {$invoice->invoice_number}", $result);
                }
            } catch (\Exception $e) {
                Log::error("Failed to apply payment offset for invoice {$invoice->invoice_number}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
