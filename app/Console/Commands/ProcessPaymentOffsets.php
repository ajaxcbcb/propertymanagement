<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\PaymentOffsetService;
use Illuminate\Console\Command;

class ProcessPaymentOffsets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-offsets {--tenant= : Process offsets for a specific tenant ID} {--preview : Preview what would happen without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process payment offsets using tenant wallet balances to settle old pending invoices (FIFO)';

    protected PaymentOffsetService $offsetService;

    public function __construct(PaymentOffsetService $offsetService)
    {
        parent::__construct();
        $this->offsetService = $offsetService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $preview = $this->option('preview');

        if ($tenantId) {
            // Process specific tenant
            return $this->processTenant($tenantId, $preview);
        }

        // Process all tenants with wallet balance
        $this->info('Processing payment offsets for all tenants with wallet balance...');
        
        $tenants = Tenant::where('wallet_balance', '>', 0)->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants with wallet balance found.');
            return;
        }

        $this->info("Found {$tenants->count()} tenant(s) with wallet balance.");

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant->id, $preview);
        }

        $this->info('✓ Payment offset processing complete.');
    }

    protected function processTenant(int $tenantId, bool $preview = false)
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant ID {$tenantId} not found.");
            return;
        }

        $this->line("─────────────────────────────────────────────────");
        $this->info("Tenant: {$tenant->name} (ID: {$tenant->id})");
        $this->info("Wallet Balance: RM " . number_format($tenant->wallet_balance, 2));

        if ($preview) {
            // Preview mode - show what would happen
            $previewData = $this->offsetService->getOffsetPreview($tenantId);
            
            if ($previewData['pending_invoices_count'] == 0) {
                $this->comment('  No pending invoices to offset.');
                return;
            }

            $this->info("  Pending Invoices: {$previewData['pending_invoices_count']}");
            $this->info("  Total Outstanding: RM " . number_format($previewData['total_outstanding'], 2));
            
            if ($previewData['can_fully_settle']) {
                $this->comment("  ✓ Can fully settle all invoices");
                $this->info("  Remaining Wallet: RM " . number_format($previewData['remaining_after_offset'], 2));
            } else {
                $this->warn("  ⚠ Cannot fully settle all invoices");
                $this->info("  Shortfall: RM " . number_format($previewData['total_outstanding'] - $previewData['wallet_balance'], 2));
            }

            $this->table(
                ['Invoice Number', 'Date', 'Amount'],
                collect($previewData['pending_invoices'])->map(function ($inv) {
                    return [
                        $inv['invoice_number'],
                        $inv['date'],
                        'RM ' . number_format($inv['amount'], 2)
                    ];
                })
            );
        } else {
            // Actual processing
            $result = $this->offsetService->processWalletOffsets($tenantId);

            if (!$result['success']) {
                $this->warn("  {$result['message']}");
                return;
            }

            $this->comment("  ✓ {$result['message']}");
            
            if (!empty($result['offsets'])) {
                foreach ($result['offsets'] as $offset) {
                    $statusIcon = $offset['status'] === 'fully_paid_from_wallet' ? '✓' : '◐';
                    $this->line("    {$statusIcon} {$offset['invoice_number']}: RM " . number_format($offset['amount_offset'], 2));
                    
                    if (isset($offset['remaining'])) {
                        $this->warn("      Remaining: RM " . number_format($offset['remaining'], 2));
                    }
                }
            }

            $this->info("  Final Wallet Balance: RM " . number_format($result['remaining_wallet'], 2));
        }
    }
}
