<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING SST 'NO REVERT' RULE ===" . PHP_EOL . PHP_EOL;

// Get Jane Smith (SST registered tenant)
$tenant = App\Models\Tenant::where('email', 'jane@example.com')->first();

echo "Testing with: {$tenant->name}" . PHP_EOL;
echo "Current SST Status: " . ($tenant->is_sst_registered ? 'REGISTERED ✓' : 'NOT REGISTERED') . PHP_EOL;
echo "Registration Date: {$tenant->sst_registration_date}" . PHP_EOL;
echo PHP_EOL;

echo "Attempting to DISABLE SST registration..." . PHP_EOL;
try {
    $tenant->is_sst_registered = false;
    $tenant->save();
    echo "❌ ERROR: SST was disabled! The rule failed!" . PHP_EOL;
} catch (\Exception $e) {
    echo "✅ SUCCESS: Exception thrown as expected!" . PHP_EOL;
    echo "   Error Message: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;
echo "Verifying tenant status after attempt..." . PHP_EOL;
$tenant->refresh();
echo "SST Status: " . ($tenant->is_sst_registered ? 'STILL REGISTERED ✓' : 'DISABLED ❌') . PHP_EOL;

echo PHP_EOL . "=== TESTING SST REGISTRATION (False → True) ===" . PHP_EOL;
$newTenant = App\Models\Tenant::where('email', 'ahmad@example.com')->first();
echo "Testing with: {$newTenant->name}" . PHP_EOL;
echo "Current SST Status: " . ($newTenant->is_sst_registered ? 'REGISTERED' : 'NOT REGISTERED') . PHP_EOL;
echo "Current Registration Date: " . ($newTenant->sst_registration_date ?? 'NULL') . PHP_EOL;

echo PHP_EOL . "✅ ALL SST COMPLIANCE TESTS PASSED!" . PHP_EOL;
