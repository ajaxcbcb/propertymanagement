<?php

namespace App\Console\Commands;

use App\Models\TenancyAgreement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckTenancyExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired tenancy agreements and mark them as inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        // 1. Deactivate expired agreements or future agreements marked as active
        $deactivated = TenancyAgreement::where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->where('end_date', '<', $today)
                      ->orWhere('start_date', '>', $today);
            })
            ->update(['is_active' => false]);

        // 2. Activate agreements that have started and are not yet active
        $activated = TenancyAgreement::where('is_active', false)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->update(['is_active' => true]);

        if ($deactivated > 0 || $activated > 0) {
            $this->info("Updated Tenancy Statuses: deactivated {$deactivated}, activated {$activated}.");
            Log::info("Tenancy Status Update: deactivated {$deactivated}, activated {$activated}.");
        } else {
            $this->info('No tenancy status updates needed.');
        }
    }
}
