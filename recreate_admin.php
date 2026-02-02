<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@admin.com';
$user = User::where('email', $email)->first();

if ($user) {
    echo "Deleting existing user $email...\n";
    $user->delete();
}

echo "Creating new admin user...\n";
$user = User::create([
    'name' => 'Admin',
    'email' => $email,
    'password' => Hash::make('password'),
]);

echo "User created.\n";
echo "Email: $email\n";
echo "Password: password\n";
