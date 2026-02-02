<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoAllScenarios extends Command
{
    protected $signature = 'demo:20-scenarios {--fresh : Refresh database structure before seeding}';
    protected $description = 'Repopulate database with 20 complex scenarios for testing/demo';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        }

        $this->info('Starting 20 Scenarios Population...');
        $this->call('db:seed', ['--class' => 'TwentyScenariosSeeder']);

        $this->newLine();
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║          20 SCENARIOS POPULATED SUCCESSFULLY              ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        
        $scenarios = [
            '1. Standard Good Tenant', '2. Corporate SST Tenant', '3. Advance Payer (Wallet)', 
            '4. Surplus Payer', '5. Grace Period User', '6. Occasional Late Payer',
            '7. Chronic Late Payer', '8. Partial Payer', '9. Struggling Tenant (Unpaid)',
            '10. Evicted Debtor', '11. Multi-Property Tenant', '12. Internal Transfer',
            '13. Wallet Manual Topup', '14. Hybrid Payer', '15. Admin Correction',
            '16. Money Pit Property', '17. Retail Shop', '18. Vacant Unit',
            '19. Renovation Project', '20. Golden Goose (Perfect)'
        ];

        foreach ($scenarios as $scenario) {
            $this->line("✓ $scenario");
        }
        
        $this->newLine();
        $this->info('Run "php artisan serve" to view the data in the dashboard.');
    }
}
