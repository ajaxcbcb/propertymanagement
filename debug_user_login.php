<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Database: " . env('DB_DATABASE') . "\n";
echo "DB Connection: " . env('DB_CONNECTION') . "\n";

$users = User::all();
echo "Total Users: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "ID: " . $user->id . ", Name: " . $user->name . ", Email: " . $user->email . "\n";
}

// Force reset specifically to be sure
$admin = User::where('email', 'admin@admin.com')->first();
if (!$admin) {
    echo "Admin not found, creating...\n";
    $admin = new User();
    $admin->name = 'System Admin';
    $admin->email = 'admin@admin.com';
} else {
    echo "Admin found, updating password...\n";
}

$admin->password = Hash::make('password'); // Let's try 'password' instead of 'admin' to be safe standard
$admin->save();

echo "Password reset to 'password' for admin@admin.com\n";
