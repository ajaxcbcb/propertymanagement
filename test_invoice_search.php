<?php

/**
 * Test script to verify invoice/receipt global search auto-filter functionality
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Invoice/Receipt Global Search Auto-Filter Test ===\n\n";

// Test 1: Get a sample invoice with tenant
echo "Test 1: Invoice with Tenant\n";
echo "----------------------------\n";

$invoice = App\Models\Invoice::whereNotNull('tenant_id')
    ->where('type', 'received')
    ->with(['tenant', 'property'])
    ->first();

if ($invoice) {
    echo "Invoice Found:\n";
    echo "  Receipt No: {$invoice->invoice_number}\n";
    echo "  Invoice ID: {$invoice->id}\n";
    echo "  Tenant: " . ($invoice->tenant ? $invoice->tenant->name : 'N/A') . " (ID: {$invoice->tenant_id})\n";
    echo "  Property: " . ($invoice->property ? $invoice->property->name : 'N/A') . "\n";
    echo "  Amount: RM " . number_format($invoice->amount_total, 2) . "\n";
    echo "  Status: {$invoice->status}\n\n";
    
    // Simulate the URL that would be generated
    $url = route('filament.admin.resources.account-statements.account-overviews.index', [
        'tenant' => $invoice->tenant_id,
    ]);
    
    echo "Generated URL:\n";
    echo "  {$url}\n\n";
    
    echo "Expected Behavior:\n";
    echo "  ✓ Redirects to Account Statements page\n";
    echo "  ✓ Tenant filter automatically applied (tenant_id = {$invoice->tenant_id})\n";
    echo "  ✓ Shows only this tenant's account statements\n";
    
} else {
    echo "No invoices with tenant found in database\n";
    echo "Run seeders to populate data: php artisan db:seed\n";
}

echo "\n";

// Test 2: Count invoices by type
echo "Test 2: Invoice Statistics\n";
echo "---------------------------\n";

$receivedCount = App\Models\Invoice::where('type', 'received')->count();
$expenditureCount = App\Models\Invoice::where('type', 'expenditure')->count();
$withTenantCount = App\Models\Invoice::whereNotNull('tenant_id')->count();
$withoutTenantCount = App\Models\Invoice::whereNull('tenant_id')->count();

echo "Total Invoices:\n";
echo "  Received (Payments): {$receivedCount}\n";
echo "  Expenditure: {$expenditureCount}\n";
echo "  With Tenant: {$withTenantCount}\n";
echo "  Without Tenant: {$withoutTenantCount}\n\n";

echo "Global Search Behavior:\n";
echo "  • Invoices with tenant → Redirect to Account Statements (filtered)\n";
echo "  • Invoices without tenant → Redirect to Invoice edit page\n";

echo "\n";

// Test 3: Sample search queries
echo "Test 3: Sample Search Queries\n";
echo "------------------------------\n";

$sampleInvoices = App\Models\Invoice::whereNotNull('tenant_id')
    ->where('type', 'received')
    ->with(['tenant'])
    ->limit(3)
    ->get();

if ($sampleInvoices->count() > 0) {
    echo "Try searching for these receipt numbers:\n\n";
    
    foreach ($sampleInvoices as $inv) {
        echo "  🔍 Search: \"{$inv->invoice_number}\"\n";
        echo "     → Will filter to: " . ($inv->tenant ? $inv->tenant->name : 'Unknown') . "\n";
        echo "     → URL: ?tenant={$inv->tenant_id}\n\n";
    }
} else {
    echo "No sample invoices available\n";
}

echo "\n";

// Test 4: Verify searchable attributes
echo "Test 4: Searchable Attributes\n";
echo "------------------------------\n";

try {
    $resource = new App\Filament\Resources\Invoices\InvoiceResource();
    $searchableAttrs = $resource::getGloballySearchableAttributes();
    
    echo "Invoice global search searches in:\n";
    foreach ($searchableAttrs as $attr) {
        echo "  • {$attr}\n";
    }
    
    echo "\n✓ You can search by receipt number or description\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n\n";

echo "HOW TO TEST:\n";
echo "1. Go to: http://127.0.0.1:8888/admin\n";
echo "2. Click global search (🔍)\n";
echo "3. Search for a receipt number (e.g., from the samples above)\n";
echo "4. Click on the search result\n";
echo "5. You should be redirected to Account Statements with tenant filter applied\n";
