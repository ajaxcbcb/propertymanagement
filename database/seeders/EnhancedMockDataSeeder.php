<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\TenancyAgreement;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EnhancedMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Cleaning up existing data...');
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Invoice::truncate();
        TenancyAgreement::truncate();
        Tenant::truncate();
        Property::truncate();
        \Illuminate\Support\Facades\DB::table('system_settings')->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        $this->command->info('✓ Database cleared.');

        $this->command->info('Creating high-volume comprehensive mock data (50 items each)...');

        // 1. Ensure System Settings exist
        SystemSetting::updateOrCreate(['key' => 'sst_rate'], [
            'value' => '8',
            'label' => 'SST Rate (%)',
            'type' => 'number'
        ]);
        SystemSetting::updateOrCreate(['key' => 'late_fee_rate'], [
            'value' => '2',
            'label' => 'Late Fee Rate (%)',
            'type' => 'number'
        ]);
        SystemSetting::updateOrCreate(['key' => 'late_fee_grace_period'], [
            'value' => '7',
            'label' => 'Late Fee Grace Period (Days)',
            'type' => 'number'
        ]);
        $this->command->info('✓ System settings initialized.');

        // 2. Create 50 Properties (Real Malaysian Data)
        $this->command->info('Generating 50 properties (Kuala Lumpur & Selangor)...');
        $realProperties = [
            ['name' => 'Suria KLCC', 'address' => '241, Suria KLCC, Kuala Lumpur City Centre, 50088 Kuala Lumpur', 'type' => 'Retail'],
            ['name' => 'Pavilion Kuala Lumpur', 'address' => '168, Bukit Bintang St, Bukit Bintang, 55100 Kuala Lumpur', 'type' => 'Retail'],
            ['name' => 'Mid Valley Megamall', 'address' => 'Lingkaran Syed Putra, Mid Valley City, 59200 Kuala Lumpur', 'type' => 'Retail'],
            ['name' => 'Petronas Twin Tower 1', 'address' => 'Tower 1, KLCC, 50088 Kuala Lumpur', 'type' => 'Office'],
            ['name' => 'Petronas Twin Tower 2', 'address' => 'Tower 2, KLCC, 50088 Kuala Lumpur', 'type' => 'Office'],
            ['name' => 'Sunway Pyramid', 'address' => '3, Jalan PJS 11/15, Bandar Sunway, 47500 Petaling Jaya, Selangor', 'type' => 'Retail'],
            ['name' => 'One Utama Shopping Centre', 'address' => '1, Lebuh Bandar Utama, Bandar Utama, 47800 Petaling Jaya, Selangor', 'type' => 'Retail'],
            ['name' => 'The Intermark', 'address' => '348, Jalan Tun Razak, 50400 Kuala Lumpur', 'type' => 'Commercial'],
            ['name' => 'Bangsar Village', 'address' => '1, Jalan Telawi 1, Bangsar Baru, 59100 Kuala Lumpur', 'type' => 'Retail'],
            ['name' => 'Menara Prudential TRX', 'address' => 'Persiaran TRX, Tun Razak Exchange, 55188 Kuala Lumpur', 'type' => 'Office'],
            ['name' => 'Mont Kiara Sofia', 'address' => 'Jalan Kiara, Mont Kiara, 50480 Kuala Lumpur', 'type' => 'Apartment'],
            ['name' => 'Nu Sentral', 'address' => '201, Jalan Tun Sambanthan, Brickfields, 50470 Kuala Lumpur', 'type' => 'Retail'],
            ['name' => 'Empire Shopping Gallery', 'address' => 'Jalan SS 16/1, 47500 Subang Jaya, Selangor', 'type' => 'Retail'],
            ['name' => 'Menara Maybank', 'address' => '100, Jalan Tun Perak, 50050 Kuala Lumpur', 'type' => 'Office'],
            ['name' => 'Publika Shopping Gallery', 'address' => '1, Jalan Dutamas 1, Solaris Dutamas, 50480 Kuala Lumpur', 'type' => 'Retail'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $base = $realProperties[$i % count($realProperties)];
            $unitNum = rand(100, 999);
            $level = rand(1, 50);
            
            Property::create([
                'name' => $base['name'] . " - Unit " . ($i + 1),
                'lot_number' => "Lot " . $unitNum . ", Level " . $level,
                'address' => $base['address'],
                'type' => $base['type'],
                'base_rent' => rand(1500, 12000),
                'status' => 'occupied'
            ]);
        }

        // 3. Create 50 Tenants
        $this->command->info('Generating 50 tenants...');
        $firstNames = ['Ahmad', 'John', 'Jane', 'Chong', 'Muthu', 'Siti', 'Robert', 'Lisa', 'Kevin', 'Sarah'];
        $lastNames = ['Ali', 'Doe', 'Smith', 'Wong', 'Kumar', 'Ibrahim', 'Williams', 'Tan', 'Chen', 'Zainal'];
        
        for ($i = 1; $i <= 50; $i++) {
            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] . ' ' . $i;
            $isSst = (rand(1, 10) > 7);
            Tenant::create([
                'name' => $name,
                'email' => Str::slug($name) . '@test.com',
                'phone' => '01' . rand(1,9) . '-' . rand(1000000, 9999999),
                'is_sst_registered' => $isSst,
                'sst_registration_date' => $isSst ? now()->subMonths(rand(1, 12)) : null,
                'wallet_balance' => rand(0, 1) ? rand(500, 10000) : 0,
            ]);
        }

        // 4. Create 50 Tenancy Agreements
        $this->command->info('Generating 50 agreements...');
        $properties = Property::all();
        $tenants = Tenant::all();

        for ($i = 0; $i < 50; $i++) {
            $property = $properties[$i];
            $tenant = $tenants[$i];
            
            TenancyAgreement::create([
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'start_date' => now()->subMonths(rand(6, 12)),
                'end_date' => now()->addMonths(rand(1, 12)),
                'agreed_rent' => $property->base_rent,
                'is_active' => true,
            ]);
        }

        // 5. Create Payments (Only Paid/Void)
        $this->command->info('Generating payment history for all agreements...');
        $agreements = TenancyAgreement::with('tenant')->get();
        $sstRate = 8;
        $paymentsCreated = 0;

        foreach ($agreements as $agreement) {
            $rentMonths = $agreement->start_date->diffInMonths(now());
            
            // For each month since start, create a payment record (some might be missing to show debt)
            for ($m = 0; $m <= $rentMonths; $m++) {
                $monthDate = $agreement->start_date->copy()->addMonths($m);
                
                // 70% chance tenant actually paid for this month
                $rand = rand(1, 10);
                if ($rand <= 7) {
                    $base = $agreement->agreed_rent;
                    $sst = $agreement->tenant->is_sst_registered ? ($base * $sstRate / 100) : 0;
                    $total = $base + $sst;

                    Invoice::create([
                        'type' => 'received',
                        'tenant_id' => $agreement->tenant_id,
                        'tenancy_agreement_id' => $agreement->id,
                        'invoice_number' => 'REC-' . str_pad($paymentsCreated + 1, 6, '0', STR_PAD_LEFT),
                        'date_received' => $monthDate->copy()->addDays(rand(0, 5)),
                        'amount_received' => $base,
                        'amount_sst' => $sst,
                        'amount_total' => $total,
                        'status' => 'paid',
                        'sst_rate_snapshot' => $sstRate,
                        'late_fee_rate_snapshot' => 2,
                        'created_at' => $monthDate,
                    ]);
                    $paymentsCreated++;
                } elseif ($rand <= 9) {
                    // 20% chance it's still unpaid (pending) -> to demonstrate debt/late fees
                    $base = $agreement->agreed_rent;
                    $sst = $agreement->tenant->is_sst_registered ? ($base * $sstRate / 100) : 0;
                    $total = $base + $sst;

                    Invoice::create([
                        'type' => 'received',
                        'tenant_id' => $agreement->tenant_id,
                        'tenancy_agreement_id' => $agreement->id,
                        'invoice_number' => 'INV-' . str_pad($paymentsCreated + 1, 6, '0', STR_PAD_LEFT),
                        'date_received' => $monthDate, // Used as due date for late fee logic
                        'amount_received' => 0,
                        'amount_sst' => $sst,
                        'amount_total' => $total,
                        'status' => 'pending',
                        'sst_rate_snapshot' => $sstRate,
                        'late_fee_rate_snapshot' => 2,
                        'created_at' => $monthDate,
                    ]);
                    $paymentsCreated++;
                } else {
                    // 10% chance it's VOID
                    $paymentsCreated++;
                    Invoice::create([
                        'type' => 'received',
                        'tenant_id' => $agreement->tenant_id,
                        'tenancy_agreement_id' => $agreement->id,
                        'invoice_number' => 'VOID-' . str_pad($paymentsCreated, 6, '0', STR_PAD_LEFT),
                        'date_received' => $monthDate,
                        'amount_received' => 0,
                        'amount_sst' => 0,
                        'amount_total' => 0,
                        'status' => 'void',
                        'sst_rate_snapshot' => $sstRate,
                        'late_fee_rate_snapshot' => 2,
                        'created_at' => $monthDate,
                    ]);
                }
            }
        }

        // 6. Create Expenditures
        $this->command->info('Generating mock expenditures (expenses)...');
        $expenseItems = ['Plumbing Repair', 'Electrician', 'Cleaning Service', 'Roof Repair', 'Security Guard', 'Painting', 'Landscaping', 'Utility Bill'];
        
        for ($i = 0; $i < 20; $i++) {
            $prop = $properties->random();
            $amount = rand(100, 3000);
            
            Invoice::create([
                'type' => 'expenditure',
                'property_id' => $prop->id,
                'invoice_number' => 'EXP-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'date_received' => now()->subDays(rand(1, 90)),
                'amount_received' => $amount,
                'amount_sst' => 0,
                'amount_total' => $amount,
                'status' => 'paid',
                'description' => $expenseItems[array_rand($expenseItems)] . ' for ' . $prop->name,
            ]);
        }
        $this->command->info('✓ 20 mock expenditure records created.');

        $this->command->info('✓ Mock data population complete!');
        $this->command->info('✓ 50 Properties, 50 Tenants, 50 Agreements.');
        $this->command->info("✓ Created {$paymentsCreated} income records (Paid/Pending/Void).");
    }
}
