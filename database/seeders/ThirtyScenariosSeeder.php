<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\TenancyAgreement;
use App\Models\Invoice;
use Carbon\Carbon;

class ThirtyScenariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Disable Events
        Invoice::unsetEventDispatcher();
        Tenant::unsetEventDispatcher();
        TenancyAgreement::unsetEventDispatcher();

        // 1. Clean Slate
        Schema::disableForeignKeyConstraints();
        Invoice::truncate();
        TenancyAgreement::truncate();
        Property::truncate();
        Tenant::truncate();
        User::truncate(); 
        Schema::enableForeignKeyConstraints();

        $this->command->info('Database cleared. Starting 30 Scenarios Seeding...');

        // 2. Create Admin User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->command->info('Created test@example.com user.');

        $now = now();

        // ════════════════════════════════════════════════════════════════════
        // GROUP A: GOOD TENANTS
        // ════════════════════════════════════════════════════════════════════

        // 1. Standard Good Tenant
        $this->createScenario(
            'Scenario 1: Standard Good Tenant',
            'Alice Standard',
            ['type' => 'Residential', 'name' => 'Unit 101'],
            ['payment_behavior' => 'ontime']
        );

        // 2. SST Registered Corporate Tenant
        $this->createScenario(
            'Scenario 2: Corporate SST Tenant',
            'TechCorp Solutions',
            ['type' => 'Commercial', 'name' => 'Office Suite A'],
            ['is_sst_registered' => true, 'payment_behavior' => 'ontime']
        );

        // 3. Advance Payer (Large Wallet Balance)
        $this->createScenario(
            'Scenario 3: The Advance Payer',
            'Bob Wealthy',
            ['type' => 'Luxury Condo', 'name' => 'Penthouse 1'],
            ['wallet_initial' => 24000, 'rent' => 4000, 'payment_behavior' => 'wallet_auto']
        );

        // 4. Surplus Payer (Rounds Up)
        $this->createScenario(
            'Scenario 4: Surplus Payer',
            'Carol Tipper',
            ['type' => 'Apartment', 'name' => 'Unit 202'],
            ['payment_behavior' => 'surplus', 'rent' => 1250] // Pays 1300 usually
        );

        // 5. Grace Period User
        $this->createScenario(
            'Scenario 5: Grace Period User',
            'Dave Deadline',
            ['type' => 'Apartment', 'name' => 'Unit 203'],
            ['payment_behavior' => 'grace_period']
        );

        // ════════════════════════════════════════════════════════════════════
        // GROUP B: CHALLENGING TENANTS
        // ════════════════════════════════════════════════════════════════════

        // 6. Occasional Late Payer
        $this->createScenario(
            'Scenario 6: Occasional Late Payer',
            'Eve Late',
            ['type' => 'Studio', 'name' => 'Studio 5'],
            ['payment_behavior' => 'late_occasional']
        );

        // 7. Chronic Late Payer (Frequent Late Fees)
        $this->createScenario(
            'Scenario 7: Chronic Late Payer',
            'Frank Fees',
            ['type' => 'Apartment', 'name' => 'Unit 305'],
            ['payment_behavior' => 'late_chronic']
        );

        // 8. Partial Payer (Always has balance)
        $this->createScenario(
            'Scenario 8: Partial Payer',
            'Grace Half',
            ['type' => 'Townhouse', 'name' => 'Unit 7B'],
            ['payment_behavior' => 'partial']
        );

        // 9. Struggling Tenant (Unpaid Rents)
        $this->createScenario(
            'Scenario 9: Struggling Tenant',
            'Harry Owe',
            ['type' => 'Apartment', 'name' => 'Unit 404'],
            ['payment_behavior' => 'unpaid']
        );

        // 10. Evicted Tenant (Inactive with Debt)
        $this->createScenario(
            'Scenario 10: Evicted Debtor',
            'Ivan Gone',
            ['type' => 'Apartment', 'name' => 'Unit 405'],
            ['status' => 'inactive', 'payment_behavior' => 'unpaid', 'end_date' => $now->copy()->subMonth()]
        );

        // ════════════════════════════════════════════════════════════════════
        // GROUP C: COMPLEX & MULTI-PROPERTY
        // ════════════════════════════════════════════════════════════════════

        // 11. Multi-Property Tenant (Residential + Comm)
        $this->createMultiPropertyScenario();

        // 12. Internal Transfer (Unit A -> Unit B)
        $this->createTransferScenario();

        // 13. Wallet Offset Lover
        $this->createScenario(
            'Scenario 13: Wallet Manual Topup',
            'Kelly Cashless',
            ['type' => 'Condo', 'name' => 'Unit 888'],
            ['payment_behavior' => 'wallet_manual']
        );

        // 14. Shared Payment (Partial Cash + Partial Wallet)
        $this->createScenario(
            'Scenario 14: Hybrid Payer',
            'Larry Mix',
            ['type' => 'Apartment', 'name' => 'Unit 555'],
            ['payment_behavior' => 'hybrid_wallet_cash']
        );

        // 15. The "Correction" (Invoice Adjustment/Refund)
        $this->createScenario(
            'Scenario 15: Admin Correction',
            'Manny Mod',
            ['type' => 'Apartment', 'name' => 'Unit 123'],
            ['special_event' => 'void_invoice']
        );

        // ════════════════════════════════════════════════════════════════════
        // GROUP D: PROPERTY & EXPENSE FOCUSED
        // ════════════════════════════════════════════════════════════════════

        // 16. High Maintenance Property
        $this->createPropertyScenario(
            'Scenario 16: Money Pit Property',
            ['type' => 'Old House', 'name' => '100 Crumbling Lane'],
            ['expense_profile' => 'high']
        );

        // 17. Commercial Retail (High Revenue, High Expense)
        $this->createPropertyScenario(
            'Scenario 17: Retail Shop',
            ['type' => 'Retail', 'name' => 'Shop G-01'],
            ['expense_profile' => 'commercial', 'tenant_name' => 'Fashion Outlet']
        );

        // 18. Vacant Unit (Expenses only, no income)
        $this->createPropertyScenario(
            'Scenario 18: Vacant Unit',
            ['type' => 'Apartment', 'name' => 'Empty Unit 99'],
            ['is_vacant' => true, 'expense_profile' => 'utilities_only']
        );

        // 19. Under Renovation
        $this->createPropertyScenario(
            'Scenario 19: Renovation Project',
            ['type' => 'Landed', 'name' => 'Fixer Upper 22'],
            ['is_vacant' => true, 'expense_profile' => 'renovation']
        );

        // 20. The "Perfect" Investment
        $this->createScenario(
            'Scenario 20: Golden Goose',
            'Olivia Owner-Dream',
            ['type' => 'Luxury Villa', 'name' => 'Palm Villa 1'],
            ['payment_behavior' => 'ontime', 'rent' => 8000, 'expense_profile' => 'low']
        );

        // 21. Late Fee "Backlog"
        $this->createScenario(
            'Scenario 21: Late Fee Catchup',
            'Ken Ketchup',
            ['type' => 'Apartment', 'name' => 'Unit 777'],
            ['payment_behavior' => 'late_fee_outstanding']
        );

        // ════════════════════════════════════════════════════════════════════
        // GROUP E: NEW SCENARIOS (22-30)
        // ════════════════════════════════════════════════════════════════════

        // 22. Early Termination w/ Penalty
        $this->createScenario(
            'Scenario 22: Early Termination',
            'Gary Go',
            ['type' => 'Apartment', 'name' => 'Unit 404B'],
            ['status' => 'inactive', 'payment_behavior' => 'early_term', 'end_date' => $now->copy()->subMonth()]
        );

        // 23. Security Deposit deduction (Simulated via Expense)
        $this->createScenario(
            'Scenario 23: Deposit Dispute',
            'Harry Hammer',
            ['type' => 'Condo', 'name' => 'Unit 303 Damaged'],
            ['status' => 'inactive', 'payment_behavior' => 'deposit_dispute', 'end_date' => $now->copy()->subWeeks(2)]
        );

        // 24. Rent Hike Renewal (2 Agreements)
        // Helper function for this specific case
        $this->createRenegotiationScenario();

        // 25. Short Term Holiday
        $this->createScenario(
            'Scenario 25: Short Term Stay',
            'Jenny June',
            ['type' => 'Villa', 'name' => 'Summer House'],
            ['payment_behavior' => 'short_term', 'status' => 'inactive', 'end_date' => $now->copy()->subDays(5)]
        );

        // 26. Rent Withholding
        $this->createScenario(
            'Scenario 26: Rent Strike',
            'Karen Complaint',
            ['type' => 'Attic', 'name' => 'Leaky Loft'],
            ['payment_behavior' => 'withholding']
        );

        // 27. Yearly Upfront Payer
        $this->createScenario(
            'Scenario 27: Yearly Upfront',
            'Larry Lump-Sum',
            ['type' => 'Mansion', 'name' => 'Estate 1'],
            ['payment_behavior' => 'upfront_yearly', 'rent' => 2000]
        );

        // 28. Messy Payer (Random small amounts)
        $this->createScenario(
            'Scenario 28: Messy Account',
            'Mike Mess',
            ['type' => 'Studio', 'name' => 'Unit 12 Basement'],
            ['payment_behavior' => 'messy']
        );

        // 29. Corporate Bulk (3 Properties)
        $this->createBulkCorporateScenario();

        // 30. No-Show / Cancelled
        $this->createScenario(
            'Scenario 30: No Show',
            'Ned Nevercame',
            ['type' => 'Apartment', 'name' => 'Ghost Unit 00'],
            ['status' => 'inactive', 'payment_behavior' => 'no_show', 'end_date' => $now->copy()->subMonths(5)]
        );

    }

    /**
     * core helper to create standard scenarios
     */
    protected function createScenario($scenarioName, $tenantName, $propData, $options = [])
    {
        $this->command->info("Seeding $scenarioName...");

        // 1. Create Property
        $prop = Property::create([
            'name' => $propData['name'],
            'address' => $propData['name'] . ', Simulation City',
            'type' => $propData['type'] ?? 'Residential',
            'base_rent' => $options['rent'] ?? 1500,
            'status' => 'occupied'
        ]);

        // 2. Create Tenant
        $tenant = Tenant::create([
            'name' => $tenantName,
            'email' => strtolower(str_replace(' ', '.', $tenantName)) . '@example.com',
            'phone' => '555-01' . rand(10, 99),
            'is_sst_registered' => $options['is_sst_registered'] ?? false,
            'wallet_balance' => $options['wallet_initial'] ?? 0,
        ]);

        // 3. Create Agreement
        $status = $options['status'] ?? 'active';
        $endDate = isset($options['end_date']) ? Carbon::parse($options['end_date']) : now()->addYear();
        
        $startDate = now()->subMonths(6);
        if ($options['payment_behavior'] ?? '' === 'short_term') {
             $startDate = $endDate->copy()->subMonths(3);
        }

        $agreement = TenancyAgreement::create([
            'tenant_id' => $tenant->id,
            'property_id' => $prop->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'agreed_rent' => $options['rent'] ?? 1500,
            'is_active' => $status === 'active'
        ]);

        // 4. Generate History
        $this->generateInvoiceHistory($agreement, $options);
    }

    protected function generateInvoiceHistory($agreement, $options)
    {
        $behavior = $options['payment_behavior'] ?? 'ontime';
        $months = 6;
        $start = now()->subMonths($months)->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $monthDate = $start->copy()->addMonths($i);
            
            // Skip future or outside agreement range
            if ($monthDate > now() && !$agreement->is_active) continue;
            // Additional check for short term or specific dates
            if ($monthDate < $agreement->start_date && $behavior !== 'no_show') continue;
            if ($monthDate > $agreement->end_date) continue;

            if ($behavior === 'no_show') return; // No invoices generated

            // Create Invoice (Rent)
            $amount = $agreement->agreed_rent;
            $invoice = Invoice::create([
                'type' => 'received', // Receivable
                'tenant_id' => $agreement->tenant_id,
                'tenancy_agreement_id' => $agreement->id,
                'property_id' => $agreement->property_id,
                'invoice_number' => 'INV-' . $monthDate->format('Ym') . '-' . $agreement->id,
                'date_received' => $monthDate, // Due date
                'amount_total' => $amount,
                'original_amount_total' => $amount,
                'status' => 'pending',
                'description' => 'Rent for ' . $monthDate->format('F Y'),
                'amount_received' => 0
            ]);

            // Handle Behaviors
            if ($behavior === 'ontime') {
                $this->payInvoice($invoice, $amount, $monthDate);
            } 
            elseif ($behavior === 'wallet_auto') {
                $this->payInvoice($invoice, $amount, $monthDate, 'wallet');
            }
            elseif ($behavior === 'surplus') {
                $this->payInvoice($invoice, $amount + 50, $monthDate);
                $agreement->tenant->increment('wallet_balance', 50);
            }
            elseif ($behavior === 'grace_period') {
                $payDate = $monthDate->copy()->addDays(3);
                $this->payInvoice($invoice, $amount, $payDate);
            }
            elseif ($behavior === 'late_occasional') {
                if ($i === 2) {
                    $lateDate = $monthDate->copy()->addDays(15);
                    $fee = $amount * 0.05;
                    $invoice->update(['late_fee_amount' => $fee, 'amount_total' => $amount + $fee, 'last_late_fee_applied_at' => $lateDate]);
                    $this->payInvoice($invoice, $amount + $fee, $lateDate);
                } else {
                    $this->payInvoice($invoice, $amount, $monthDate);
                }
            }
            elseif ($behavior === 'late_chronic') {
                 $lateDate = $monthDate->copy()->addDays(rand(10, 20));
                 $fee = $amount * 0.05;
                 $invoice->update(['late_fee_amount' => $fee, 'amount_total' => $amount + $fee, 'last_late_fee_applied_at' => $lateDate]);
                 $this->payInvoice($invoice, $amount + $fee, $lateDate);
            }
            elseif ($behavior === 'late_fee_outstanding') {
                if ($i === 5) {
                    $lateDate = $monthDate->copy()->addDays(10);
                    $fee = $amount * 0.10;
                    $invoice->update(['late_fee_amount' => $fee, 'amount_total' => $amount + $fee, 'last_late_fee_applied_at' => $lateDate, 'status' => 'partial', 'amount_received' => $amount]);
                    $this->createPaymentRecord($invoice, $amount, $lateDate, 'Rent Payment (Late Fee Skipped)');
                } else {
                    $this->payInvoice($invoice, $amount, $monthDate);
                }
            }
            elseif ($behavior === 'unpaid' || $behavior === 'withholding') {
                if ($monthDate->addDays(5) < now()) {
                    $fee = $amount * 0.05;
                    $invoice->update(['late_fee_amount' => $fee, 'amount_total' => $amount + $fee, 'last_late_fee_applied_at' => now(), 'original_amount_total' => $amount]);
                }
            }
            elseif ($behavior === 'partial') {
                $paid = $amount / 2;
                $invoice->update(['status' => 'partial', 'amount_received' => $paid, 'amount_total' => $amount]);
                $this->createPaymentRecord($invoice, $paid, $monthDate);
            }
            elseif ($behavior === 'early_term') {
                // Last month pay penalty
                $this->payInvoice($invoice, $amount, $monthDate);
                // If it's the last loop, add penalty invoice
            }
            elseif ($behavior === 'deposit_dispute') {
                $this->payInvoice($invoice, $amount, $monthDate);
            }
            elseif ($behavior === 'short_term') {
                $this->payInvoice($invoice, $amount, $monthDate);
            }
            elseif ($behavior === 'upfront_yearly') {
                // If first month, pay HUGE amount
                if ($i === 0) {
                    $totalYear = $amount * 12;
                     $invoice->update(['status' => 'paid', 'amount_received' => $amount]);
                     // Record huge payment
                     $this->createPaymentRecord($invoice, $totalYear, $monthDate, 'Yearly Upfront Payment');
                     $agreement->tenant->update(['wallet_balance' => $totalYear - $amount]);
                } else {
                    // Subsequent months paid from wallet (simulated)
                     $invoice->update(['status' => 'paid', 'amount_received' => $amount]);
                     $agreement->tenant->decrement('wallet_balance', $amount);
                }
            }
             elseif ($behavior === 'messy') {
                // Random payments
                 $invoice->update(['status' => 'partial', 'amount_received' => 0]);
                 $randPay = rand(100, $amount);
                 $this->createPaymentRecord($invoice, $randPay, $monthDate->addDays(rand(1,10)), 'Random Payment');
                 $invoice->update(['amount_received' => $randPay]);
            }
        }
        
        // Post-loop specific logic
        if ($behavior === 'early_term') {
             Invoice::create(['type' => 'received', 'tenant_id' => $agreement->tenant_id, 'tenancy_agreement_id' => $agreement->id, 'property_id' => $agreement->property_id, 'invoice_number' => 'PENALTY-' . rand(100,999), 'date_received' => now(), 'amount_total' => 2000, 'amount_received' => 2000, 'status' => 'paid', 'description' => 'Early Termination Penalty']);
        }
        if ($behavior === 'deposit_dispute') {
             Invoice::create(['type' => 'expenditure', 'property_id' => $agreement->property_id, 'amount_total' => 1500, 'amount_received' => 1500, 'status' => 'paid', 'invoice_number' => 'REP-'.rand(100,999), 'date_received' => now(), 'description' => 'Major Repairs - Wall Damage']);
        }
        
        // ... (inside generateInvoiceHistory)
        // Handle Expense Profile if any
        if (isset($options['expense_profile'])) {
            $this->seedExpenses($agreement->property, $options['expense_profile'], $agreement);
        }
    }

    protected function createMultiPropertyScenario()
    {
        $this->command->info("Seeding Scenario 11: Multi-Property...");
        
        $tenant = Tenant::create([
            'name' => 'Mr. Monopoly',
            'email' => 'monopoly@example.com',
            'phone' => '123-444-5555'
        ]);

        // Prop 1: Residential
        $p1 = Property::create(['name' => 'Monopoly House', 'type' => 'Residential', 'base_rent' => 2000, 'address' => 'Boardwalk 1']);
        TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $p1->id, 'start_date' => now()->subMonths(6), 'end_date' => now()->addYear(), 'agreed_rent' => 2000, 'is_active' => true
        ]);

        // Prop 2: Commercial
        $p2 = Property::create(['name' => 'Monopoly Hotel', 'type' => 'Commercial', 'base_rent' => 10000, 'address' => 'Park Place 1']);
        TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $p2->id, 'start_date' => now()->subMonths(3), 'end_date' => now()->addYear(), 'agreed_rent' => 10000, 'is_active' => true
        ]);
    }

    protected function createTransferScenario()
    {
        $this->command->info("Seeding Scenario 12: Internal Transfer...");
        $tenant = Tenant::create(['name' => 'Jenny Transfer', 'email' => 'jenny@example.com', 'phone' => '867-5309']);
        
        // Old Prop
        $p1 = Property::create(['name' => 'Old Apartment 4A', 'type' => 'Apartment', 'base_rent' => 1200, 'address' => '4th St']);
        $ta1 = TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $p1->id, 
            'start_date' => now()->subYear(), 'end_date' => now()->subMonth(), // Ended
            'agreed_rent' => 1200, 'is_active' => false
        ]);
        // simulate history
        
        // New Prop
        $p2 = Property::create(['name' => 'New Condo 5B', 'type' => 'Condo', 'base_rent' => 1500, 'address' => '5th Ave']);
        $ta2 = TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $p2->id, 
            'start_date' => now()->subMonth(), 'end_date' => now()->addYear(), // Active
            'agreed_rent' => 1500, 'is_active' => true
        ]);
    }

    protected function createRenegotiationScenario()
    {
        $this->command->info("Seeding Scenario 24: Rent Hike Renewal...");
        $tenant = Tenant::create(['name' => 'Ian Inflation', 'email' => 'ian@example.com', 'phone' => '999-0000']);
        $prop = Property::create(['name' => 'Unit 500', 'type' => 'Apartment', 'base_rent' => 1200, 'address' => 'Inflation Lane']);
        
        // Old Agreement
        TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $prop->id, 
            'start_date' => now()->subYear(), 'end_date' => now()->subDay(),
            'agreed_rent' => 1000, 'is_active' => false
        ]);
        
        // New Agreement
        TenancyAgreement::create([
            'tenant_id' => $tenant->id, 'property_id' => $prop->id, 
            'start_date' => now(), 'end_date' => now()->addYear(),
            'agreed_rent' => 1200, 'is_active' => true
        ]);
    }

    protected function createBulkCorporateScenario()
    {
        $this->command->info("Seeding Scenario 29: Bulk Corporate...");
        $tenant = Tenant::create(['name' => 'Big Corp Inc', 'email' => 'admin@bigcorp.com', 'phone' => '555-9999', 'is_sst_registered' => true]);
        
        foreach(['A', 'B', 'C'] as $unit) {
            $prop = Property::create(['name' => "Corp Unit $unit", 'type' => 'Commercial', 'base_rent' => 3000, 'address' => "Corp Park $unit"]);
            TenancyAgreement::create([
                'tenant_id' => $tenant->id, 'property_id' => $prop->id, 
                'start_date' => now()->subMonths(2), 'end_date' => now()->addYear(),
                'agreed_rent' => 3000, 'is_active' => true
            ]);
        }
    }

    protected function createPropertyScenario($scenarioName, $propData, $options)
    {
         $this->command->info("Seeding $scenarioName...");
         $prop = Property::create([
            'name' => $propData['name'],
            'address' => $propData['name'] . ', Simulation City',
            'type' => $propData['type'],
            'base_rent' => 0,
            'status' => ($options['is_vacant'] ?? false) ? 'vacant' : 'occupied'
        ]);

        $agreement = null;

        // Ensure Money Pit (Scenario 16) has a tenant if not vacant
        if (!isset($options['tenant_name']) && !($options['is_vacant'] ?? false)) {
             $options['tenant_name'] = 'John Doe (Default)';
        }

        if (isset($options['tenant_name'])) {
            $tenant = Tenant::create(['name' => $options['tenant_name'], 'email' => 'tenant'.rand(100,999).'@example.com', 'phone' => '111-2222']);
            $agreement = TenancyAgreement::create([
                'tenant_id' => $tenant->id, 'property_id' => $prop->id, 'start_date' => now()->subMonths(12), 'end_date' => now()->addMonths(24), 'agreed_rent' => 5000, 'is_active' => true
            ]);
        }

        $this->seedExpenses($prop, $options['expense_profile'] ?? 'low', $agreement);
    }

    protected function seedExpenses($property, $profile, $agreement = null)
    {
         $count = match($profile) {
             'high' => 15,
             'commercial' => 8,
             'utilities_only' => 6,
             'renovation' => 10,
             default => 2
         };

         for ($i = 0; $i < $count; $i++) {
             $amount = match($profile) {
                 'renovation' => rand(1000, 5000),
                 'utilities_only' => rand(50, 150),
                 'commercial' => rand(500, 2000),
                 default => rand(100, 500)
             };

             $desc = match($profile) {
                 'renovation' => ['Contractor Installment', 'Materials', 'Painting', 'Flooring'][$i % 4],
                 'utilities_only' => ['Electric Bill', 'Water Bill'][$i % 2],
                 'commercial' => ['HVAC Maintenance', 'Security Service', 'Cleaning'][$i % 3],
                 'high' => ['Plumbing Fix', 'Roof Leak', 'Termite Treatment', 'Broken Window', 'Heater Repair'][$i % 5],
                 default => 'General Maintenance'
             };

             Invoice::create([
                 'type' => 'expenditure',
                 'property_id' => $property->id,
                 'tenant_id' => $agreement ? $agreement->tenant_id : null,
                 'tenancy_agreement_id' => $agreement ? $agreement->id : null,
                 'amount_total' => $amount,
                 'original_amount_total' => $amount,
                 'amount_received' => $amount, 
                 'amount_sst' => 0,
                 'status' => 'paid',
                 'invoice_number' => 'EXP-' . rand(10000, 99999),
                 'date_received' => now()->subDays(rand(1, 180)),
                 'description' => $desc
             ]);
         }
    }

    protected function payInvoice($invoice, $amount, $date, $method = 'cash')
    {
        $invoice->update([
            'status' => 'paid',
            'amount_received' => $invoice->amount_total
        ]);

        Invoice::create([
            'type' => 'received',
            'tenant_id' => $invoice->tenant_id,
            'tenancy_agreement_id' => $invoice->tenancy_agreement_id,
            'property_id' => $invoice->property_id,
            'invoice_number' => 'PAY-' . str_replace('INV-', '', $invoice->invoice_number),
            'date_received' => $date,
            'amount_total' => $amount,
            'amount_received' => $amount,
            'status' => 'paid', 
            'original_amount_total' => $amount,
            'description' => 'Payment for ' . $invoice->invoice_number . ($method === 'wallet' ? ' (Wallet)' : '')
        ]);
    }
    
    protected function createPaymentRecord($invoice, $amount, $date, $desc = 'Partial Payment') {
         Invoice::create([
            'type' => 'received',
            'tenant_id' => $invoice->tenant_id,
            'tenancy_agreement_id' => $invoice->tenancy_agreement_id,
            'property_id' => $invoice->property_id,
            'invoice_number' => 'PAY-' . rand(10000,99999),
            'date_received' => $date,
            'amount_total' => $amount,
            'amount_received' => $amount,
            'status' => 'paid',
            'original_amount_total' => $amount,
            'description' => $desc
        ]);
    }
}
