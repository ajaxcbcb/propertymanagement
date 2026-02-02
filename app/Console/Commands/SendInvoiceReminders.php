<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for invoices due in 15 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = now()->addDays(15)->toDateString();
        
        $this->info("Checking for unpaid invoices due on: {$targetDate}");

        $invoices = \App\Models\Invoice::where('status', '!=', 'paid')
            ->whereDate('due_date', $targetDate)
            ->with(['tenant', 'tenancyAgreement.property'])
            ->get();

        if ($invoices->isEmpty()) {
            $this->info("No invoices found due in 15 days.");
            return;
        }

        foreach ($invoices as $invoice) {
            $tenant = $invoice->tenant;
            
            if (!$tenant) continue;

            $this->info("Sending reminder to: {$tenant->name} ({$tenant->email})");

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Dear {$tenant->name},\n\n This is a gentle reminder that Invoice #{$invoice->invoice_number} for {$invoice->amount_total} is due on {$invoice->due_date}.\n\n Please arrange for payment.",
                    function ($message) use ($tenant) {
                        $message->to($tenant->email)
                            ->subject('Invoice Payment Reminder');
                    }
                );
                $this->info("✅ Email sent.");
            } catch (\Exception $e) {
                $this->error("❌ Failed to send email: " . $e->getMessage());
            }
        }
        
        $this->info("All reminders processed.");
    }
}
