<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\SystemSetting;
use App\Models\TenancyAgreement;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active tenancy agreements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting invoice generation...');

        // Get all active tenancy agreements
        $agreements = TenancyAgreement::where('is_active', true)
            ->with(['tenant', 'property'])
            ->get();

        if ($agreements->isEmpty()) {
            $this->warn('No active tenancy agreements found.');
            return 0;
        }

        $invoicesCreated = 0;
        $totalAmount = 0;

        foreach ($agreements as $agreement) {
            $tenant = $agreement->tenant;
            $baseAmount = $agreement->agreed_rent;
            $sstAmount = 0;

            $sstRate = 0;
            // Calculate SST if tenant is registered
            if ($tenant->is_sst_registered) {
                // Get configured SST rate (default to 8 if not found)
                $sstRate = SystemSetting::get('sst_rate', 8);
                $sstMultiplier = floatval($sstRate) / 100;
                
                $sstAmount = $baseAmount * $sstMultiplier;
            }

            // Get current late fee rate to snapshot for this invoice
            $lateFeeRate = SystemSetting::get('late_fee_rate', 2);

            $totalInvoiceAmount = $baseAmount + $sstAmount;

            // Generate unique invoice number
            $invoiceNumber = 'INV-' . date('Ym') . '-' . str_pad($agreement->id, 5, '0', STR_PAD_LEFT);

            // Create the invoice record
            $invoice = Invoice::create([
                'type' => 'received',
                'tenant_id' => $tenant->id,
                'tenancy_agreement_id' => $agreement->id,
                'property_id' => $agreement->property_id,
                'invoice_number' => $invoiceNumber,
                'date_received' => now(), // Issued date
                'amount_total' => $totalInvoiceAmount,
                'original_amount_total' => $totalInvoiceAmount,
                'amount_received' => 0,
                'amount_sst' => $sstAmount,
                'sst_rate_snapshot' => $sstRate,
                'late_fee_rate_snapshot' => $lateFeeRate,
                'status' => 'pending',
                'description' => 'Rent for ' . now()->format('F Y'),
            ]);

            $invoicesCreated++;
            $totalAmount += $totalInvoiceAmount;

            $this->info("  + Generated Invoice {$invoiceNumber} for {$tenant->name} (RM {$totalInvoiceAmount})");

            // Wallet deduction logic (Auto-pay)
            if ($tenant->wallet_balance > 0) {
                $paymentAmount = min($tenant->wallet_balance, $totalInvoiceAmount);
                $tenant->wallet_balance -= $paymentAmount;
                $tenant->save();

                // Update invoice status
                $invoice->amount_received = $paymentAmount;
                if ($paymentAmount >= $totalInvoiceAmount) {
                    $invoice->status = 'paid';
                    $invoice->amount_total = 0;
                    $this->comment("    ✓ Auto-paid from wallet: RM {$paymentAmount}");
                } else {
                    $invoice->status = 'partial';
                    $invoice->amount_total = $totalInvoiceAmount - $paymentAmount; // Remaining balance?? 
                    // Wait, logic says amount_total is Usually outstanding? 
                    // In `PaymentOffsetService`, partial payment:
                    // $invoice->amount_total = $outstandingAmount - $availableAmount;
                    // So yes, reduce total ?? Or keep total and track received?
                    // PaymentOffsetService reduces total. So we follow that pattern.
                    // But then we lose original amount? No, we have original_amount_total.
                    
                    $invoice->amount_total = $totalInvoiceAmount - $paymentAmount;
                    $this->comment("    ⚠ Partial pay from wallet: RM {$paymentAmount}");
                }
                $invoice->save();
             
                // We do NOT create a separate "Payment" invoice row for the wallet deduction here 
                // because we just updated the main invoice. 
                // UNLESS the system expects separate Receipt rows?
                // The `PaymentOffsetService` updates the EXISTING invoice.
                // So this logic is correct.
            }
        }

        $this->newLine();
        $this->info("✓ Successfully generated {$invoicesCreated} invoices");
        $this->info("✓ Total amount: RM " . number_format($totalAmount, 2));

        return 0;
    }
}
