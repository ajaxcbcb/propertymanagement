<?php

use App\Models\SystemSetting;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenancyAgreement;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Verifying Business Logic & Snapshotting ---\n";

// 1. Setup Test Data
$tenant = Tenant::create([
    'name' => 'Test Tenant Logic',
    'email' => 'test_logic@example.com',
    'phone' => '555-' . rand(1000, 9999),
    'is_sst_registered' => true
]);

$agreement = TenancyAgreement::create([
    'tenant_id' => $tenant->id,
    'property_id' => 1, // Assumption
    'agreed_rent' => 1000,
    'status' => 'active',
    'start_date' => now(),
    'end_date' => now()->addYear()
]);

// 2. Scenario A: Current Logic (SST 8%, Late Fee 2%)
echo "\n[Scenario A] Setting SST to 8%, Late Fee to 2%...\n";
SystemSetting::updateOrCreate(['key' => 'sst_rate'], ['value' => '8', 'type' => 'number']);
SystemSetting::updateOrCreate(['key' => 'late_fee_rate'], ['value' => '2', 'type' => 'number']);

// Simulate Invoice Generation (Copying logic from GenerateInvoices)
$sstRate = SystemSetting::get('sst_rate', 8);
$lateFeeRate = SystemSetting::get('late_fee_rate', 2);
$baseAmount = $agreement->agreed_rent;
$sstAmount = $baseAmount * ($sstRate / 100);
$total = $baseAmount + $sstAmount;

$invoiceA = Invoice::create([
    'tenant_id' => $tenant->id,
    'tenancy_agreement_id' => $agreement->id,
    'invoice_number' => 'TEST-001',
    'due_date' => now()->subDays(60), // Overdue
    'amount_base' => $baseAmount,
    'amount_sst' => $sstAmount,
    'amount_total' => $total,
    'status' => 'unpaid',
    'sst_rate_snapshot' => $sstRate,        // <--- SNAPSHOT
    'late_fee_rate_snapshot' => $lateFeeRate // <--- SNAPSHOT
]);

echo "Invoice A Created: Total RM $total (SST Snapshot: {$invoiceA->sst_rate_snapshot}%, Late Fee Snapshot: {$invoiceA->late_fee_rate_snapshot}%)\n";

// 3. Scenario B: Change Logic (SST 10%, Late Fee 5%)
echo "\n[Scenario B] Changing System Settings (SST -> 10%, Late Fee -> 5%)...\n";
SystemSetting::updateOrCreate(['key' => 'sst_rate'], ['value' => '10', 'type' => 'number']);
SystemSetting::updateOrCreate(['key' => 'late_fee_rate'], ['value' => '5', 'type' => 'number']);

// Simulate Invoice B Generation
$sstRateB = SystemSetting::get('sst_rate');
$lateFeeRateB = SystemSetting::get('late_fee_rate');
$sstAmountB = $baseAmount * ($sstRateB / 100);
$totalB = $baseAmount + $sstAmountB;

$invoiceB = Invoice::create([
    'tenant_id' => $tenant->id,
    'tenancy_agreement_id' => $agreement->id,
    'invoice_number' => 'TEST-002',
    'due_date' => now()->addDays(30),
    'amount_base' => $baseAmount,
    'amount_sst' => $sstAmountB,
    'amount_total' => $totalB,
    'status' => 'unpaid',
    'sst_rate_snapshot' => $sstRateB,      // <--- NEW SNAPSHOT
    'late_fee_rate_snapshot' => $lateFeeRateB // <--- NEW SNAPSHOT
]);

echo "Invoice B Created: Total RM $totalB (SST Snapshot: {$invoiceB->sst_rate_snapshot}%, Late Fee Snapshot: {$invoiceB->late_fee_rate_snapshot}%)\n";

// 4. Verify Integrity
echo "\n[Integrity Check]\n";
$freshA = Invoice::find($invoiceA->id);
if ($freshA->sst_rate_snapshot == 8 && $freshA->late_fee_rate_snapshot == 2) {
    echo "✅ Invoice A retained original rates (8% / 2%) despite system update.\n";
} else {
    echo "❌ Invoice A Snapshot Corrupted!\n";
}

if ($invoiceB->sst_rate_snapshot == 10 && $invoiceB->late_fee_rate_snapshot == 5) {
    echo "✅ Invoice B used new rates (10% / 5%).\n";
} else {
    echo "❌ Invoice B Snapshot Incorrect!\n";
}

// 5. Test Late Fee Application Logic
echo "\n[Testing Late Fee Application on Invoice A]\n";
// Invoice A is overdue. It has a snapshot of 2%. Global setting is 5%.
// Logic should use 2%.

$rateToUse = $freshA->late_fee_rate_snapshot > 0 ? $freshA->late_fee_rate_snapshot : SystemSetting::get('late_fee_rate');
echo "Logic determined Late Fee Rate to use: {$rateToUse}%\n";

if ($rateToUse == 2) {
    echo "✅ Correctly using snapshot rate (2%) instead of current global rate (5%).\n";
} else {
    echo "❌ Failed: Logic attempting to use wrong rate ($rateToUse%).\n";
}

// Cleanup
$invoiceA->delete();
$invoiceB->delete();
$agreement->delete();
$tenant->delete();

// Restore default
SystemSetting::updateOrCreate(['key' => 'sst_rate'], ['value' => '8']);
SystemSetting::updateOrCreate(['key' => 'late_fee_rate'], ['value' => '2']);
echo "\nSystem settings restored to defaults.\n";
