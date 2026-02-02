<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\TenancyAgreement;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Initializing Simulation Data...');

        Schema::disableForeignKeyConstraints();
        Invoice::truncate();
        TenancyAgreement::truncate();
        Tenant::truncate();
        Property::truncate();
        DB::table('system_settings')->delete();
        Schema::enableForeignKeyConstraints();

        // 0. Settings
        SystemSetting::create(['key' => 'sst_rate', 'value' => '8', 'label' => 'SST Rate (%)', 'type' => 'number']);
        SystemSetting::create(['key' => 'late_fee_rate', 'value' => '2', 'label' => 'Late Fee Rate (%)', 'type' => 'number']);
        SystemSetting::create(['key' => 'late_fee_grace_period', 'value' => '7', 'label' => 'Late Fee Grace Period (Days)', 'type' => 'number']);

        // --- SCENARIO 1: The Good Tenant (Paid on time, SST Registered) ---
        $this->command->info('Creating Scenario 1: Good Tenant (SST Registered)...');
        $prop1 = Property::create([
            'name' => 'Scenario 1 - Suria KLCC',
            'lot_number' => 'Lot 101',
            'address' => 'Suria KLCC, Kuala Lumpur',
            'type' => 'Retail',
            'base_rent' => 5000,
            'status' => 'occupied'
        ]);
        $tenant1 = Tenant::create([
            'name' => 'Tenant One (Good)',
            'email' => 'good@simulation.com',
            'phone' => '011-11111111',
            'is_sst_registered' => true,
            'sst_registration_date' => now()->subYear(),
            'wallet_balance' => 0,
        ]);
        $ag1 = TenancyAgreement::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $prop1->id,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addMonths(6),
            'agreed_rent' => 5000,
            'is_active' => true,
        ]);
        // All invoices paid
        for ($i = 0; $i < 6; $i++) {
            $month = now()->subMonths(6 - $i);
            Invoice::create([
                'type' => 'received',
                'tenant_id' => $tenant1->id,
                'tenancy_agreement_id' => $ag1->id,
                'invoice_number' => 'REC-S1-' . ($i + 1),
                'date_received' => $month->copy()->addDays(1),
                'amount_received' => 5000,
                'amount_sst' => 400, // 8% of 5000
                'amount_total' => 5400,
                'status' => 'paid',
                'description' => 'Monthly Rent',
                'sst_rate_snapshot' => 8,
                'created_at' => $month,
            ]);
        }

        // --- SCENARIO 2: The Late Payer (Have Pending Invoices & Late Fees) ---
        $this->command->info('Creating Scenario 2: Late Tenant...');
        $prop2 = Property::create([
            'name' => 'Scenario 2 - Mid Valley',
            'lot_number' => 'Lot 202',
            'address' => 'Mid Valley, Kuala Lumpur',
            'type' => 'Retail',
            'base_rent' => 3000,
            'status' => 'occupied'
        ]);
        $tenant2 = Tenant::create([
            'name' => 'Tenant Two (Late)',
            'email' => 'late@simulation.com',
            'phone' => '012-22222222',
            'is_sst_registered' => false,
            'wallet_balance' => 0,
        ]);
        $ag2 = TenancyAgreement::create([
            'tenant_id' => $tenant2->id,
            'property_id' => $prop2->id,
            'start_date' => now()->subMonths(4),
            'end_date' => now()->addMonths(8),
            'agreed_rent' => 3000,
            'is_active' => true,
        ]);
        // First 2 months paid, last 2 months pending (overdue)
        for ($i = 0; $i < 4; $i++) {
            $month = now()->subMonths(4 - $i);
            $status = ($i < 2) ? 'paid' : 'pending';
            
            Invoice::create([
                'type' => 'received',
                'tenant_id' => $tenant2->id,
                'tenancy_agreement_id' => $ag2->id,
                'invoice_number' => 'INV-S2-' . ($i + 1),
                'date_received' => $month, // For pending, this is essentially the due date
                'amount_received' => ($status == 'paid') ? 3000 : 0,
                'amount_sst' => 0,
                'amount_total' => 3000,
                'status' => $status,
                'description' => 'Monthly Rent',
                'created_at' => $month,
            ]);
        }

        // --- SCENARIO 3: The Wallet User (Advance Payments) ---
        $this->command->info('Creating Scenario 3: Rich Tenant (Wallet)...');
        $prop3 = Property::create([
            'name' => 'Scenario 3 - Pavilion',
            'lot_number' => 'Lot 303',
            'address' => 'Pavilion, Kuala Lumpur',
            'type' => 'Retail',
            'base_rent' => 8000,
            'status' => 'occupied'
        ]);
        $tenant3 = Tenant::create([
            'name' => 'Tenant Three (Rich)',
            'email' => 'rich@simulation.com',
            'phone' => '013-33333333',
            'is_sst_registered' => false,
            'wallet_balance' => 25000, // Huge balance
        ]);
        $ag3 = TenancyAgreement::create([
            'tenant_id' => $tenant3->id,
            'property_id' => $prop3->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(10),
            'agreed_rent' => 8000,
            'is_active' => true,
        ]);
        // Just started, paid 1 month
        Invoice::create([
            'type' => 'received',
            'tenant_id' => $tenant3->id,
            'tenancy_agreement_id' => $ag3->id,
            'invoice_number' => 'REC-S3-1',
            'date_received' => now()->subMonths(2)->addDays(1),
            'amount_received' => 8000,
            'amount_total' => 8000,
            'status' => 'paid',
            'created_at' => now()->subMonths(2),
        ]);

        // --- SCENARIO 4: Expiring Tenancy ---
        $this->command->info('Creating Scenario 4: Expiring Tenancy...');
        $prop4 = Property::create([
            'name' => 'Scenario 4 - Sunway Pyramid',
            'lot_number' => 'Lot 404',
            'address' => 'Sunway Pyramid, Selangor',
            'type' => 'Retail',
            'base_rent' => 4500,
            'status' => 'occupied'
        ]);
        $tenant4 = Tenant::create([
            'name' => 'Tenant Four (Expiring)',
            'email' => 'expiring@simulation.com',
            'phone' => '014-44444444',
            'is_sst_registered' => false,
            'wallet_balance' => 0,
        ]);
        $ag4 = TenancyAgreement::create([
            'tenant_id' => $tenant4->id,
            'property_id' => $prop4->id,
            'start_date' => now()->subMonths(11),
            'end_date' => now()->addDays(5), // Expires in 5 days
            'agreed_rent' => 4500,
            'is_active' => true,
        ]);
        // Mostly paid
        for ($i = 0; $i < 11; $i++) {
            $month = now()->subMonths(11 - $i);
            Invoice::create([
                'type' => 'received',
                'tenant_id' => $tenant4->id,
                'tenancy_agreement_id' => $ag4->id,
                'invoice_number' => 'REC-S4-' . ($i + 1),
                'date_received' => $month->addDays(1),
                'amount_received' => 4500,
                'amount_total' => 4500,
                'status' => 'paid',
                'created_at' => $month,
            ]);
        }

        // --- SCENARIO 5: Maintenance Heavy Property ---
        $this->command->info('Creating Scenario 5: Maintenance Property...');
        $prop5 = Property::create([
            'name' => 'Scenario 5 - Old Shop',
            'lot_number' => 'Lot 505',
            'address' => 'Jalan TAR, Kuala Lumpur',
            'type' => 'Commercial',
            'base_rent' => 2000,
            'status' => 'vacant'
        ]);
        // Add random expenditures
        $expenses = ['Roof Leak', 'Plumbing', 'Repaint Walls', 'Door Fix', 'Aircon Service'];
        foreach ($expenses as $idx => $desc) {
            Invoice::create([
                'type' => 'expenditure',
                'property_id' => $prop5->id,
                'invoice_number' => 'EXP-S5-' . ($idx + 1),
                'date_received' => now()->subMonths(rand(1, 6)),
                'amount_received' => rand(200, 1500), // cost
                'amount_total' => rand(200, 1500),
                'status' => 'paid',
                'description' => $desc,
                'created_at' => now()->subMonths(rand(1,6)),
            ]);
        }
        
        // --- SCENARIO 6: Surplus Payment (Offset Old Bills + Wallet Credit) ---
        $this->command->info('Creating Scenario 6: Surplus Payment Tenant...');
        $prop6 = Property::create([
            'name' => 'Scenario 6 - KLCC Office',
            'lot_number' => 'Lot 606',
            'address' => 'KLCC, Kuala Lumpur',
            'type' => 'Office',
            'base_rent' => 2500,
            'status' => 'occupied'
        ]);
        $tenant6 = Tenant::create([
            'name' => 'Tenant Six (Surplus Payer)',
            'email' => 'surplus@simulation.com',
            'phone' => '016-66666666',
            'is_sst_registered' => false,
            'wallet_balance' => 0,
        ]);
        $ag6 = TenancyAgreement::create([
            'tenant_id' => $tenant6->id,
            'property_id' => $prop6->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(9),
            'agreed_rent' => 2500,
            'is_active' => true,
        ]);
        
        // Create 3 months of invoices: 1 paid, 2 pending
        for ($i = 0; $i < 3; $i++) {
            $month = now()->subMonths(3 - $i);
            $status = ($i < 1) ? 'paid' : 'pending';
            
            Invoice::create([
                'type' => 'received',
                'tenant_id' => $tenant6->id,
                'tenancy_agreement_id' => $ag6->id,
                'invoice_number' => 'INV-S6-' . ($i + 1),
                'date_received' => $month,
                'amount_received' => ($status == 'paid') ? 2500 : 0,
                'amount_sst' => 0,
                'amount_total' => 2500,
                'original_amount_total' => 2500,
                'status' => $status,
                'description' => 'Monthly Rent',
                'created_at' => $month,
            ]);
        }
        
        $this->command->info('  → Tenant has 2 pending invoices (RM 2,500 each = RM 5,000 total)');
        $this->command->info('  → Will demonstrate surplus payment of RM 7,000');
        $this->command->info('  → Expected: Pay both bills (RM 5,000) + RM 2,000 to wallet');

        // --- Add some random filler data ---
        $this->command->info('Adding filler data...');
        // (Simplified copy of EnhancedMockData logic for 10 more props)
        for ($p=0; $p<10; $p++) {
            Property::create([
                'name' => "Random Prop $p",
                'address' => "Address $p",
                'type' => 'Residential',
                'base_rent' => rand(1000, 5000),
                'status' => 'vacant'
            ]);
        }

        $this->command->info('Simulation Data Ready!');
    }
}
