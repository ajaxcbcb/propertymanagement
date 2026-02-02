<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::first();
if ($user) {
    $user->name = 'System Admin';
    $user->email = 'admin@admin.com';
    $user->password = Hash::make('admin');
    $user->save();
    
    echo "User updated successfully.\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
} else {
    // Create if not exists
    $user = User::create([
        'name' => 'System Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('admin'),
    ]);
    echo "User created successfully.\n";
}
