<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use App\Models\TenancyAgreement;
use App\Models\Invoice;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Console\Command;

class SendCollectionAlertNotifications extends Command
{
    protected $signature = 'notifications:collection-alerts';
    protected $description = 'Send database notifications for late or partial payments';

    public function handle()
    {
        $this->info("Checking for collection alerts...");

        $agreements = TenancyAgreement::query()
            ->where('is_active', true)
            ->with(['tenant', 'property'])
            ->get();

        $alerts = [];

        foreach ($agreements as $agreement) {
            $troubledInvoice = Invoice::where('tenancy_agreement_id', $agreement->id)
                ->where('type', 'received')
                ->whereIn('status', ['pending', 'partial', 'overdue', 'unpaid'])
                ->orderBy('date_received', 'asc')
                ->first();

            if ($troubledInvoice) {
                $balance = $this->calculateBalance($agreement);
                
                if ($balance > 0) {
                    $isLate = $troubledInvoice->date_received->isPast();
                    $isPartial = $troubledInvoice->status === 'partial';

                    if ($isLate || $isPartial) {
                        $days = now()->startOfDay()->diffInDays($troubledInvoice->date_received->startOfDay(), false);
                        $type = $isPartial ? 'Partial' : 'Late';
                        $urgency = $days < 0 ? abs($days) . ' days overdue' : ($days == 0 ? 'due today' : 'due in ' . $days . ' days');
                        
                        $alerts[] = [
                            'tenant' => $agreement->tenant->name,
                            'property' => $agreement->property->name,
                            'type' => $type,
                            'urgency' => $urgency,
                            'amount' => number_format($balance, 2),
                            'tenant_id' => $agreement->tenant_id,
                            'property_id' => $agreement->property_id,
                        ];
                    }
                }
            }
        }

        if (empty($alerts)) {
            $this->info("No alerts found.");
            return;
        }

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        foreach ($admins as $admin) {
            $this->info("Updating alerts for: {$admin->name}");

            // Clear ALL previous collection alerts to avoid duplication
            DB::table('notifications')
                ->where('notifiable_id', $admin->id)
                ->where('data', 'like', '%Collection Alert%')
                ->delete();

            foreach ($alerts as $alert) {
                // Construct the notification object
                $notification = Notification::make()
                    ->title("Collection Alert: {$alert['tenant']}")
                    ->body("{$alert['type']} payment for {$alert['property']} (RM {$alert['amount']}) is {$alert['urgency']}")
                    ->warning()
                    ->persistent()
                    ->actions([
                        Action::make('view_statement')
                            ->label('View Statement')
                            ->button()
                            ->url("/admin/account-statements/account-overviews?tenant={$alert['tenant_id']}&property={$alert['property_id']}")
                    ]);
                
                // Get the array payload that Filament expects
                $data = $notification->toDatabase($admin);
                
                // Manually insert into the database
                $id = \Illuminate\Support\Str::uuid()->toString();
                
                DB::table('notifications')->insert([
                    'id' => $id,
                    'type' => 'Filament\Notifications\Notification',
                    'notifiable_type' => get_class($admin), // 'App\Models\User'
                    'notifiable_id' => $admin->id,
                    'data' => json_encode($data),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info("Processed " . count($alerts) . " alerts.");
    }

    protected function calculateBalance($record)
    {
        $months = $record->start_date->diffInMonths(now()) + 1;
        if ($record->end_date && $record->end_date->isPast()) {
             $months = $record->start_date->diffInMonths($record->end_date) + 1;
        }
        $base = $record->agreed_rent * $months;
        $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
        $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
        
        $totalLateFees = Invoice::where('tenancy_agreement_id', $record->id)
            ->where('type', 'received')
            ->sum('late_fee_amount');
            
        $totalCharges = $base + $sst + $totalLateFees;
        
        $hasPayments = Invoice::where('tenancy_agreement_id', $record->id)
            ->where('invoice_number', 'like', 'PAY-%')
            ->exists();

        if ($hasPayments) {
            $totalPaid = Invoice::where('tenancy_agreement_id', $record->id)
                ->where('type', 'received')
                ->where('status', 'paid')
                ->where('invoice_number', 'like', 'PAY-%')
                ->sum('amount_total');
        } else {
            $totalPaid = Invoice::where('tenancy_agreement_id', $record->id)
                ->where('type', 'received')
                ->where('status', 'paid')
                ->sum('amount_received');
        }
            
        return max(0, $totalCharges - $totalPaid);
    }
}
