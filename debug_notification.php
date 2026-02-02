<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Filament\Notifications\Notification;
use App\Models\User;

try {
    $user = User::first();
    echo "User ID: " . $user->id . "\n";
    
    $notification = Notification::make()
        ->title('Script Test')
        ->body('Body')
        ->warning();
        
    $notification->sendToDatabase($user);
    
    $count = Illuminate\Support\Facades\DB::table('notifications')->count();
    echo "Notifications count: " . $count . "\n";
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
