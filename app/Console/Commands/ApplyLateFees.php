<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplyLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:apply-late-fees';
    protected $description = 'Apply late fees to overdue invoices based on system settings';

    public function handle()
    {
        $this->info("Checking for overdue invoices...");

        $lateFeeRate = \App\Models\SystemSetting::get('late_fee_rate', 2);
        $gracePeriod = \App\Models\SystemSetting::get('late_fee_grace_period', 0);
        
        $multiplier = floatval($lateFeeRate) / 100;
        
        // Find overdue, unpaid/partial invoices
        // Logic: 
        // 1. Status is not paid
        // 2. Due date + grace period < NOW
        // 3. EITHER (last_late_fee_applied_at is NULL) OR (last_late_fee_applied_at < NOW - 30 days)
        
        $cutoffDate = now()->subDays((int)$gracePeriod);
        
        $invoices = \App\Models\Invoice::where('type', 'received')
            ->whereNotIn('status', ['paid', 'void'])
            ->where('date_received', '<', $cutoffDate)
            ->where(function ($query) {
                $query->whereNull('last_late_fee_applied_at')
                      ->orWhere('last_late_fee_applied_at', '<', now()->subDays(30));
            })
            ->get();

        if ($invoices->isEmpty()) {
            $this->info("No invoices eligible for late fees.");
            return;
        }

        foreach ($invoices as $invoice) {
            // Determine rate to use: Invoice Snapshot OR Global Setting
            $rateToUse = $invoice->late_fee_rate_snapshot > 0 
                ? $invoice->late_fee_rate_snapshot 
                : $lateFeeRate;

            $multiplier = floatval($rateToUse) / 100;

            // Calculate outstanding amount (Total - Paid is not explicitly tracked if partial, 
            // but for simplicity we assume full amount if unpaid, need logic for partial later or add 'amount_paid' column.
            // For now, applying to current total amount)
            
            // NOTE: In a real system, we should track 'amount_paid'. 
            // Since our current simplified system only has 'status', 
            // we will stick to: Late Fee = Rate * Current Total Amount
            
            $fee = $invoice->amount_total * $multiplier;
            
            $invoice->amount_total += $fee;
            $invoice->late_fee_amount += $fee;
            $invoice->last_late_fee_applied_at = now();
            if (!$invoice->late_fee_duration_start) {
                $invoice->late_fee_duration_start = $invoice->date_received;
            }
            // Ensure snapshot is set if it wasn't before (optional, but good for consistency)
            if ($invoice->late_fee_rate_snapshot == 0) {
                 $invoice->late_fee_rate_snapshot = $rateToUse;
            }
            $invoice->save();
            
            $this->info("Applied late fee to {$invoice->invoice_number} (@ $rateToUse%): RM " . number_format($fee, 2) . " (New Total: RM " . number_format($invoice->amount_total, 2) . ")");
        }
        
        $this->info("Late fee application complete.");
    }
}
