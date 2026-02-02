<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class TestNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'id' => '123',
            'data' => 'test'
        ];
    }
}

try {
    $user = User::first();
    $user->notify(new TestNotification());
    
    $count = Illuminate\Support\Facades\DB::table('notifications')->count();
    echo "Notifications count (Standard): " . $count . "\n";
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
