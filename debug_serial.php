<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Models\User;

try {
    $user = User::first();
    
    $notification = Notification::make()
        ->title('Debug Title')
        ->body('Debug Body')
        ->warning()
        ->actions([
            Action::make('view')
                ->label('View')
                ->url('http://example.com')
        ]);
        
    $data = $notification->toDatabase($user);
    echo json_encode($data, JSON_PRETTY_PRINT);
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
