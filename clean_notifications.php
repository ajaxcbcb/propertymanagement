<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = Illuminate\Support\Facades\DB::table('notifications')->delete();
    echo "Deleted $count notifications.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
