<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\TenancyAgreement;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test data...');

        // Create Properties
        $property1 = Property::create([
            'name' => 'Unit 5A, Block B',
            'type' => 'Apartment',
            'base_rent' => 1500.00,
            'status' => 'vacant'
        ]);
        $this->command->info('✓ Property created: ' . $property1->name);

        $property2 = Property::create([
            'name' => 'House 12, Jalan Merdeka',
            'type' => 'House',
            'base_rent' => 2500.00,
            'status' => 'vacant'
        ]);
        $this->command->info('✓ Property created: ' . $property2->name);

        // Create Tenant WITHOUT SST registration
        $tenant1 = Tenant::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0123456789',
            'is_sst_registered' => false,
            'wallet_balance' => 2000.00
        ]);
        $this->command->info('✓ Tenant created: ' . $tenant1->name . ' (No SST, Wallet: RM ' . $tenant1->wallet_balance . ')');

        // Create Tenant WITH SST registration
        $tenant2 = Tenant::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '0198765432',
            'is_sst_registered' => true,
            'wallet_balance' => 5000.00
        ]);
        $this->command->info('✓ Tenant created: ' . $tenant2->name . ' (SST Registered on: ' . $tenant2->sst_registration_date . ', Wallet: RM ' . $tenant2->wallet_balance . ')');

        // Create Tenant with insufficient wallet balance
        $tenant3 = Tenant::create([
            'name' => 'Ahmad Ali',
            'email' => 'ahmad@example.com',
            'phone' => '0176543210',
            'is_sst_registered' => true,
            'wallet_balance' => 500.00
        ]);
        $this->command->info('✓ Tenant created: ' . $tenant3->name . ' (SST Registered, Low Wallet: RM ' . $tenant3->wallet_balance . ')');

        // Create Tenancy Agreements
        $agreement1 = TenancyAgreement::create([
            'tenant_id' => $tenant1->id,
            'property_id' => $property1->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'agreed_rent' => 1500.00,
            'is_active' => true
        ]);
        $this->command->info('✓ Agreement created: ' . $tenant1->name . ' → ' . $property1->name);

        $agreement2 = TenancyAgreement::create([
            'tenant_id' => $tenant2->id,
            'property_id' => $property2->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'agreed_rent' => 2500.00,
            'is_active' => true
        ]);
        $this->command->info('✓ Agreement created: ' . $tenant2->name . ' → ' . $property2->name);

        // Update property statuses
        $property1->update(['status' => 'occupied']);
        $property2->update(['status' => 'occupied']);

        $this->command->newLine();
        $this->command->info('✓ Test data created successfully!');
        $this->command->info('✓ 2 Properties, 3 Tenants, 2 Active Agreements');
        $this->command->newLine();
        $this->command->warn('Next step: Run "php artisan invoices:generate" to create invoices');
    }
}
