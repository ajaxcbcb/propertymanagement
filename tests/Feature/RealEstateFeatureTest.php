<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Property;
use App\Models\TenancyAgreement;
use App\Models\Invoice;
use App\Services\PaymentOffsetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class RealEstateFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_advance_payment_automatic_offset()
    {
        // Setup Tenant with Wallet Balance
        $tenant = Tenant::create([
            'name' => 'Rich Tenant',
            'email' => 'rich@test.com',
            'phone' => '123456789',
            'wallet_balance' => 3000,
            'is_sst_registered' => false,
        ]);

        $property = Property::create([
            'name' => 'Luxury Condo',
            'lot_number' => 'A-10-1',
            'address' => 'Jalan Test',
            'city' => 'KL',
            'state' => 'WP',
            'postal_code' => '50000',
            'country' => 'Malaysia',
            'type' => 'condo',
            'base_rent' => 1000,
            'status' => 'vacant',
        ]);

        $agreement = TenancyAgreement::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addYear(),
            'agreed_rent' => 1000,
            'is_active' => true,
        ]);

        // Run Invoices Generation
        Artisan::call('invoices:generate');

        // Verify Invoice Created
        $invoice = Invoice::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($invoice);
        
        // Assert Invoice is Paid
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->amount_total, 'Amount total should be 0 when fully paid from wallet'); 
        $this->assertEquals(1000, $invoice->amount_received);

        // Assert Wallet Reduced
        $tenant->refresh();
        $this->assertEquals(2000, $tenant->wallet_balance);
    }

    public function test_fifo_payment_offset()
    {
        $tenant = Tenant::create([
            'name' => 'Late Tenant',
            'email' => 'late@test.com',
            'phone' => '987654321',
            'wallet_balance' => 0,
        ]);

        // Invoice 1 (Jan) - Oldest
        $invoice1 = Invoice::create([
            'type' => 'received',
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-001',
            'date_received' => Carbon::parse('2025-01-01'),
            'amount_total' => 1000,
            'original_amount_total' => 1000,
            'amount_received' => 0,
            'status' => 'pending'
        ]);

        // Invoice 2 (Feb) - Newest
        $invoice2 = Invoice::create([
            'type' => 'received',
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-002',
            'date_received' => Carbon::parse('2025-02-01'),
            'amount_total' => 1000,
            'original_amount_total' => 1000,
            'amount_received' => 0,
            'status' => 'pending'
        ]);

        // Pay 1500 (Cover Jan fully, Feb partial)
        $payment = Invoice::create([
            'type' => 'received',
            'tenant_id' => $tenant->id,
            'invoice_number' => 'PAY-001',
            'date_received' => now(),
            'amount_total' => 0,
            'amount_received' => 1500,
            'status' => 'paid'
        ]);

        $service = new PaymentOffsetService();
        $service->applyPaymentOffset($payment);

        $invoice1->refresh();
        $invoice2->refresh();

        // Jan should be paid
        $this->assertEquals('paid', $invoice1->status, 'Invoice 1 should be paid');
        $this->assertEquals(0, $invoice1->amount_total, 'Invoice 1 amount_total should be 0');
        $this->assertEquals(1000, $invoice1->amount_received, 'Invoice 1 amount_received should be 1000');

        // Feb should be partial
        $this->assertEquals('partial', $invoice2->status, 'Invoice 2 should be partial');
        $this->assertEquals(500, $invoice2->amount_total, 'Invoice 2 amount_total should be 500');
        $this->assertEquals(500, $invoice2->amount_received, 'Invoice 2 amount_received should be 500');
    }

    public function test_renewal_process()
    {
        $tenant = Tenant::create([
            'name' => 'Renewing Tenant', 
            'email' => 'renew@test.com',
            'phone' => '0123456789'
        ]);
        $property = Property::create(['name' => 'House A', 'lot_number' => '123', 'type' => 'house', 'base_rent' => 1000]);
        
        $oldAgreement = TenancyAgreement::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'start_date' => now()->subYear(),
            'end_date' => now()->subDay(),
            'agreed_rent' => 1000,
            'is_active' => true,
        ]);

        // Simulate Renewal Action logic
        $oldAgreement->update(['is_active' => false]);
        
        $newAgreement = TenancyAgreement::create([
             'tenant_id' => $oldAgreement->tenant_id,
             'property_id' => $oldAgreement->property_id,
             'start_date' => now(),
             'end_date' => now()->addYear(),
             'agreed_rent' => 1200,
             'is_active' => true,
        ]);

        $oldAgreement->refresh();

        $this->assertFalse((bool)$oldAgreement->is_active);
        $this->assertEquals(1000, $oldAgreement->agreed_rent);
        
        $this->assertTrue((bool)$newAgreement->is_active);
        $this->assertEquals(1200, $newAgreement->agreed_rent);
        $this->assertNotEquals($oldAgreement->id, $newAgreement->id);
    }
}
