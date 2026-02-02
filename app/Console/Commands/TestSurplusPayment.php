<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\PaymentOffsetService;
use Illuminate\Console\Command;

class TestSurplusPayment extends Command
{
    protected $signature = 'test:surplus-payment';
    protected $description = 'Demonstrate surplus payment offset scenario';

    protected PaymentOffsetService $offsetService;

    public function __construct(PaymentOffsetService $offsetService)
    {
        parent::__construct();
        $this->offsetService = $offsetService;
    }

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  SURPLUS PAYMENT OFFSET DEMONSTRATION (Scenario 6)');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Find Tenant Six
        $tenant = Tenant::where('email', 'surplus@simulation.com')->first();
        
        if (!$tenant) {
            $this->error('Tenant Six not found. Please run: php artisan db:seed --class=SimulationSeeder');
            return;
        }

        // Show initial state
        $this->info("📋 INITIAL STATE");
        $this->line("─────────────────────────────────────────────────");
        $this->info("Tenant: {$tenant->name}");
        $this->info("Wallet Balance: RM " . number_format($tenant->wallet_balance, 2));
        $this->newLine();

        // Show pending invoices
        $pendingInvoices = Invoice::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->orderBy('date_received', 'asc')
            ->get();

        $this->info("Pending Invoices:");
        $this->table(
            ['Invoice #', 'Date', 'Amount', 'Status'],
            $pendingInvoices->map(fn($inv) => [
                $inv->invoice_number,
                $inv->date_received->format('Y-m-d'),
                'RM ' . number_format($inv->amount_total, 2),
                strtoupper($inv->status)
            ])
        );

        $totalOutstanding = $pendingInvoices->sum('amount_total');
        $this->warn("Total Outstanding: RM " . number_format($totalOutstanding, 2));
        $this->newLine();

        // Simulate surplus payment
        $surplusAmount = 7000;
        $this->info("💰 PROCESSING SURPLUS PAYMENT");
        $this->line("─────────────────────────────────────────────────");
        $this->comment("Creating payment record: RM " . number_format($surplusAmount, 2));
        $this->newLine();

        // Delete existing test invoice if it exists
        Invoice::where('invoice_number', 'REC-SURPLUS-TEST')->delete();

        // Create the payment (this will trigger the observer)
        $payment = Invoice::create([
            'type' => 'received',
            'tenant_id' => $tenant->id,
            'tenancy_agreement_id' => $tenant->tenancyAgreements()->first()->id,
            'invoice_number' => 'REC-SURPLUS-TEST',
            'date_received' => now(),
            'amount_received' => $surplusAmount,
            'amount_sst' => 0,
            'amount_total' => $surplusAmount,
            'original_amount_total' => $surplusAmount,
            'status' => 'paid',
            'description' => 'Surplus Payment Test - Offset Demo'
        ]);

        $this->info("✓ Payment created: {$payment->invoice_number}");
        $this->newLine();

        // Wait a moment for observer to process
        sleep(1);

        // Show final state
        $this->info("📊 FINAL STATE (After Offset)");
        $this->line("─────────────────────────────────────────────────");
        
        // Refresh tenant
        $tenant->refresh();
        
        // Get updated invoices
        $updatedInvoices = Invoice::where('tenant_id', $tenant->id)
            ->whereIn('invoice_number', ['INV-S6-2', 'INV-S6-3'])
            ->orderBy('date_received', 'asc')
            ->get();

        $this->table(
            ['Invoice #', 'Original', 'Paid', 'Remaining', 'Status'],
            $updatedInvoices->map(fn($inv) => [
                $inv->invoice_number,
                'RM ' . number_format($inv->original_amount_total ?? $inv->amount_total, 2),
                'RM ' . number_format($inv->amount_received, 2),
                'RM ' . number_format($inv->amount_total, 2),
                strtoupper($inv->status)
            ])
        );

        $this->newLine();
        $this->info("Tenant Wallet Balance: RM " . number_format($tenant->wallet_balance, 2));
        $this->newLine();

        // Summary
        $this->info("📈 SUMMARY");
        $this->line("─────────────────────────────────────────────────");
        $this->line("Payment Received:     RM " . number_format($surplusAmount, 2));
        $this->line("Applied to Bills:     RM " . number_format($totalOutstanding, 2));
        $this->line("Added to Wallet:      RM " . number_format($surplusAmount - $totalOutstanding, 2));
        $this->newLine();

        if ($tenant->wallet_balance == ($surplusAmount - $totalOutstanding)) {
            $this->comment("✓ SUCCESS! Payment offset logic working correctly!");
        } else {
            $this->warn("⚠ Wallet balance doesn't match expected value.");
            $this->warn("Expected: RM " . number_format($surplusAmount - $totalOutstanding, 2));
            $this->warn("Actual: RM " . number_format($tenant->wallet_balance, 2));
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
    }
}
