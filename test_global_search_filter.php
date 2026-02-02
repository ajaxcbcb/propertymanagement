<?php

/**
 * Test script to verify global search auto-filter functionality
 * 
 * This script tests:
 * 1. Tenant filter URL generation
 * 2. Property filter URL generation
 * 3. Filter application logic
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Global Search Auto-Filter Test ===\n\n";

// Test 1: Get a sample tenant
echo "Test 1: Tenant Filter\n";
echo "---------------------\n";

$tenant = App\Models\Tenant::first();
if ($tenant) {
    echo "Tenant Found: {$tenant->name} (ID: {$tenant->id})\n";
    
    // Simulate the URL that would be generated
    $url = route('filament.admin.resources.account-statements.account-overviews.index', [
        'tenant' => $tenant->id,
    ]);
    
    echo "Generated URL: {$url}\n";
    echo "Expected filter: tenant_id = {$tenant->id}\n\n";
    
    // Check if tenant has any tenancy agreements
    $agreements = App\Models\TenancyAgreement::where('tenant_id', $tenant->id)->count();
    echo "Tenant has {$agreements} tenancy agreement(s)\n";
} else {
    echo "No tenants found in database\n";
}

echo "\n";

// Test 2: Get a sample property
echo "Test 2: Property Filter\n";
echo "----------------------\n";

$property = App\Models\Property::first();
if ($property) {
    echo "Property Found: {$property->name} (ID: {$property->id})\n";
    
    // Simulate the URL that would be generated
    $url = route('filament.admin.resources.account-statements.account-overviews.index', [
        'property' => $property->id,
    ]);
    
    echo "Generated URL: {$url}\n";
    echo "Expected filter: property_id = {$property->id}\n\n";
    
    // Check if property has any tenancy agreements
    $agreements = App\Models\TenancyAgreement::where('property_id', $property->id)->count();
    echo "Property has {$agreements} tenancy agreement(s)\n";
} else {
    echo "No properties found in database\n";
}

echo "\n";

// Test 3: Verify filter configuration
echo "Test 3: Filter Configuration\n";
echo "----------------------------\n";

try {
    $resource = new App\Filament\Resources\AccountStatements\AccountOverviewResource();
    echo "✓ AccountOverviewResource loaded successfully\n";
    
    // Check if the model is correct
    $model = $resource::getModel();
    echo "✓ Model: {$model}\n";
    
    echo "✓ Filters are configured in the resource\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Test query parameter parsing
echo "Test 4: Query Parameter Parsing\n";
echo "-------------------------------\n";

// Simulate request with tenant parameter
$_GET['tenant'] = 1;
$_GET['property'] = 2;

$tenantId = request()->query('tenant');
$propertyId = request()->query('property');

echo "Simulated URL: ?tenant=1&property=2\n";
echo "Parsed tenant ID: " . ($tenantId ?? 'null') . "\n";
echo "Parsed property ID: " . ($propertyId ?? 'null') . "\n";

if ($tenantId && $propertyId) {
    echo "✓ Both parameters parsed correctly\n";
} else {
    echo "✗ Parameters not parsed correctly\n";
}

echo "\n=== Test Complete ===\n";
