<?php

namespace App\Filament\Widgets;

use App\Models\TenancyAgreement;
use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TopDebtors extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Collection Alerts';

    public function mount(): void
    {
        // Check for urgent collection alerts
        $urgentAgreements = TenancyAgreement::query()
            ->where('is_active', true)
            ->with(['tenant', 'property', 'invoices'])
            ->get()
            ->filter(fn ($record) => $this->calculateBalance($record) > 0)
            ->map(function ($record) {
                // Filter eagerly loaded invoices
                $troubledInvoice = $record->invoices
                    ->where('type', 'received')
                    ->whereIn('status', ['pending', 'partial', 'overdue', 'unpaid'])
                    ->filter(function ($invoice) {
                        return $invoice->date_received < now();
                    })
                    ->sortBy('date_received')
                    ->first();
                    
                if ($troubledInvoice) {
                    $record->troubled_invoice = $troubledInvoice;
                    return $record;
                }
                return null;
            })
            ->filter();

        $urgentCount = $urgentAgreements->count();

        if ($urgentCount > 0) {
            // 1. Show Floating Toast
            \Filament\Notifications\Notification::make()
                ->title('Urgent Collection Alerts')
                ->body("There are {$urgentCount} tenants with late or partial payments requiring attention.")
                ->danger()
                ->send();

            // 2. Persist to Database for the current user (if not already there)
            $user = auth()->user();
            
            if ($user) {
                foreach ($urgentAgreements as $agreement) {
                    $uniqueId = 'collection-alert-' . $agreement->tenant_id . '-' . $agreement->property_id . '-' . today()->format('Ymd');
                    $cacheKey = "notification_{$user->id}_{$uniqueId}";
                    
                    if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                        // Check if this specific daily alert exists to avoid duplicate stacking on refresh
                        $exists = DB::table('notifications')
                            ->where('notifiable_id', $user->id)
                            ->where('notifiable_type', get_class($user))
                            ->where('data', 'like', '%' . $uniqueId . '%')
                            ->exists();
                            
                        if (!$exists) {
                            $invoice = $agreement->troubled_invoice;
                            $days = now()->startOfDay()->diffInDays($invoice->date_received->startOfDay(), false);
                            $type = $invoice->status === 'partial' ? 'Partial' : 'Late';
                            $urgency = $days < 0 ? abs($days) . ' days overdue' : 'Due today';
                            $balance = number_format($this->calculateBalance($agreement), 2);

                            // Create notification object using correct Actions namespace
                            $notification = \Filament\Notifications\Notification::make()
                                ->title("Collection Alert: {$agreement->tenant->name}")
                                ->body("{$type} payment for {$agreement->property->name} (RM {$balance}) is {$urgency}")
                                ->warning()
                                ->actions([
                                    \Filament\Actions\Action::make('view_statement')
                                        ->label('View Statement')
                                        ->button()
                                        ->url("/admin/account-statements/account-overviews?tenant={$agreement->tenant_id}&property={$agreement->property_id}")
                                ])
                                ->persistent();

                            // Use toArray() to get the flat payload which Filament expects in the data column
                            $data = $notification->toArray();
                            $data['format'] = 'filament'; // Explicitly set format
                            
                            // Manually insert
                            $id = (string) \Illuminate\Support\Str::uuid();
                            
                            DB::table('notifications')->insert([
                                'id' => $id,
                                'type' => 'Filament\Notifications\Notification',
                                'notifiable_type' => get_class($user),
                                'notifiable_id' => $user->id,
                                'data' => json_encode($data),
                                'read_at' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            
                            // Cache for 24 hours
                            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDay());
                        } else {
                            // If it exists in DB but not cache, verify and cache it to save future DB hits
                            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDay());
                        }
                    }
                }
            }
        }
    }

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => 
                TenancyAgreement::query()
                    ->where('is_active', true)
                    ->with(['tenant', 'property', 'invoices'])
                    ->get()
                    ->map(function ($record) {
                        // Identify the oldest outstanding invoice to ensure a value for Urgency
                        // Use the eagerly loaded invoices collection
                        $troubledInvoice = $record->invoices
                            ->where('type', 'received')
                            ->whereIn('status', ['pending', 'partial', 'overdue', 'unpaid'])
                            ->sortBy('date_received')
                            ->first();

                        $record->calculated_balance = $this->calculateBalance($record);
                        
                        // Need to manually recreate date_received object if it's not automatically cast when accessing via collection
                        // (Models typically handle casts, so it should be fine)
                        
                        if ($troubledInvoice && $troubledInvoice->status === 'partial') {
                            $record->priority_cat = 1; // Partial
                        } elseif ($troubledInvoice && $troubledInvoice->date_received < now()) {
                            $record->priority_cat = 2; // Late
                        } elseif ($troubledInvoice && $troubledInvoice->date_received->diffInDays(now()) <= 3) {
                            $record->priority_cat = 2.5; // Due Soon
                        } else {
                            $record->priority_cat = 3; // Upcoming / Debt
                        }
                        
                        // Always provide a date for urgency if an invoice exists
                        $record->priority_date = $troubledInvoice ? $troubledInvoice->date_received : null;

                        return $record;
                    })
                    ->filter(fn ($record) => $record->calculated_balance > 0)
                    ->sort(function ($a, $b) {
                        if ($a->priority_cat !== $b->priority_cat) {
                            return $a->priority_cat <=> $b->priority_cat;
                        }
                        if ($a->priority_date && $b->priority_date) {
                            return $a->priority_date <=> $b->priority_date;
                        }
                        return $b->calculated_balance <=> $a->calculated_balance;
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('Property')
                    ->size('xs')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgency')
                    ->getStateUsing(function ($record) {
                        if (!$record->priority_date) return 'Outstanding Balance';
                        
                        $days = now()->startOfDay()->diffInDays($record->priority_date->startOfDay(), false);
                        
                        if ($days < 0) {
                            return abs($days) . ' days overdue';
                        } elseif ($days === 0) {
                            return 'Due today';
                        } else {
                            return 'Due in ' . $days . ' days';
                        }
                    })
                    ->color(function ($record) {
                        if (!$record->priority_date) return 'gray';
                        $days = now()->startOfDay()->diffInDays($record->priority_date->startOfDay(), false);
                        return $days <= 0 ? 'danger' : 'warning';
                    }),
                Tables\Columns\TextColumn::make('status_info')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match ((float)$record->priority_cat) {
                            1.0 => 'Partial',
                            2.0 => 'Late',
                            2.5 => 'Due Soon',
                            3.0 => 'Debt',
                            default => 'Debt',
                        };
                    })
                    ->color(fn ($state) => match ($state) {
                        'Partial' => 'warning',
                        'Late' => 'danger',
                        'Due Soon' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('calculated_balance')
                    ->label('Owed')
                    ->money('MYR')
                    ->color('danger')
                    ->alignment('right'),
            ])
            ->actions([
                \Filament\Actions\Action::make('view_statement')
                    ->label('Statement')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->size('xs')
                    ->url(fn ($record) => "/admin/account-statements/account-overviews?tenant={$record->tenant_id}&property={$record->property_id}"),
            ])
            ->paginated(false);
    }

    protected function calculateBalance($record)
    {
        // Use eager loaded invoices if available, fallback to query if not (safety)
        $invoices = $record->relationLoaded('invoices') ? $record->invoices : $record->invoices()->get();

        $months = $record->start_date->diffInMonths(now()) + 1;
        if ($record->end_date && $record->end_date->isPast()) {
             $months = $record->start_date->diffInMonths($record->end_date) + 1;
        }
        $base = $record->agreed_rent * $months;
        // Use cached setting
        $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
        $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
        
        // Calculate late fees from collection
        $totalLateFees = $invoices
            ->where('type', 'received')
            ->sum('late_fee_amount');
            
        $totalCharges = $base + $sst + $totalLateFees;
        
        // Count only actual payments (start with PAY-) or invoices marked paid 
        $hasPayments = $invoices
            ->filter(fn($inv) => str_starts_with($inv->invoice_number, 'PAY-'))
            ->isNotEmpty();

        if ($hasPayments) {
            $totalPaid = $invoices
                ->where('type', 'received')
                ->where('status', 'paid')
                ->filter(fn($inv) => str_starts_with($inv->invoice_number, 'PAY-'))
                ->sum('amount_total');
        } else {
            // Fallback for manual marking without PAY records
            $totalPaid = $invoices
                ->where('type', 'received')
                ->where('status', 'paid')
                ->sum('amount_received');
        }
            
        return max(0, $totalCharges - $totalPaid);
    }
}
