<?php

namespace App\Filament\Resources\AccountStatements;

use App\Filament\Resources\AccountStatements\Pages\ListAccountOverview;
use App\Models\TenancyAgreement;
use App\Models\Invoice;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountStatementMail;
use Filament\Notifications\Notification;

class AccountOverviewResource extends Resource
{
    protected static ?string $model = TenancyAgreement::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string | \UnitEnum | null $navigationGroup = 'Finance';
    
    public static function getNavigationLabel(): string
    {
        return 'Account Statements';
    }

    public static function getModelLabel(): string
    {
        return 'Statement';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $terms = explode(' ', $search);
                        
                        foreach ($terms as $term) {
                            $term = trim($term);
                            if (empty($term)) continue;
                            
                            $query->where(function (Builder $q) use ($term) {
                                $q->whereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$term}%"))
                                  ->orWhereHas('property', fn ($p) => $p->where('name', 'like', "%{$term}%"));
                            });
                        }
                        
                        return $query;
                    })
                    ->sortable(),
                TextColumn::make('property.name')
                    ->label('Property')
                    ->searchable(false) // Handled by Tenant column's custom query above
                    ->sortable(),
                TextColumn::make('total_rent_due')
                    ->label('Total Charges')
                    ->money('MYR')
                    ->getStateUsing(function ($record) {
                        // Calculate total charges: 
                        // Simplified logic: Agreed Rent * Months active
                        $months = $record->start_date->diffInMonths(now()) + 1;
                        if ($record->end_date && $record->end_date->isPast()) {
                             $months = $record->start_date->diffInMonths($record->end_date) + 1;
                        }
                        
                        $base = $record->agreed_rent * $months;
                        
                        // Add SST if applicable (Simplified: assuming current rate for history as a placeholder, 
                        // but ideally we should sum actual charges)
                        $sstAmount = 0;
                        if ($record->tenant->is_sst_registered) {
                             $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                             $sstAmount = $base * ($sstRate / 100);
                        }
                        
                        $lateFees = $record->invoices()
                             ->where('type', 'received')
                             ->sum('late_fee_amount');
                        
                        return $base + $sstAmount + $lateFees;
                    }),
                TextColumn::make('total_paid')
                    ->label('Total Paid')
                    ->money('MYR')
                    ->getStateUsing(function ($record) {
                        return Invoice::where('tenancy_agreement_id', $record->id)
                            ->where('type', 'received')
                            ->where('status', 'paid')
                            ->sum('amount_total');
                    }),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('MYR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : ($state < 0 ? 'success' : 'gray'))
                    ->getStateUsing(function ($record) {
                        // Charges - Payments
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
                        
                        $totalPaid = Invoice::where('tenancy_agreement_id', $record->id)
                            ->where('type', 'received')
                            ->where('status', 'paid')
                            ->sum('amount_total');
                            
                        return $totalCharges - $totalPaid;
                    }),
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Charges - Payments
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
                        
                        $totalPaid = Invoice::where('tenancy_agreement_id', $record->id)
                            ->where('type', 'received')
                            ->where('status', 'paid')
                            ->sum('amount_total');
                            
                        $balance = $totalCharges - $totalPaid;
                        
                        if ($balance > 0) return 'Overdue';
                        if ($balance < 0) return 'Advance Payment';
                        if (round($balance, 2) == 0) return 'On Track';
                        return 'Error';
                    })
                    ->color(fn ($state) => match ($state) {
                        'Overdue' => 'danger',
                        'Advance Payment' => 'success',
                        'On Track' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('is_active')
                    ->label('Agreement Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Ended'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_active')
                    ->label('Agreement Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Ended',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('property_id')
                    ->label('Property')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Action::make('view_statement')
                    ->label('View Statement')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading('Tenant Account Statement')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalContent(function ($record) {
                        $transactions = static::getTransactions($record);
                        $totalCharges = collect($transactions)->sum('debit');
                        $totalPaid = collect($transactions)->sum('credit');
                        $balance = $totalCharges - $totalPaid;
                        
                        return view('filament.resources.account-statements.statement-modal', [
                            'record' => $record,
                            'transactions' => $transactions,
                            'totalCharges' => $totalCharges,
                            'totalPaid' => $totalPaid,
                            'balance' => $balance,
                        ]);
                    }),

                Action::make('email_statement')
                    ->label('Email Statement')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn () => \App\Models\SystemSetting::get('mail_enabled', '0') === '1')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Recipient Email')
                            ->email()
                            ->required()
                            ->default(fn ($record) => $record->tenant->email),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Custom Message (Optional)')
                            ->rows(3)
                            ->placeholder('Add a personal note to the email...'),
                    ])
                    ->action(function ($record, array $data) {
                        \App\Services\MailConfigurationService::apply();
                        
                        $transactions = static::getTransactions($record);
                        $totalCharges = collect($transactions)->sum('debit');
                        $totalPaid = collect($transactions)->sum('credit');
                        $balance = $totalCharges - $totalPaid;

                        try {
                            Mail::to($data['email'])->send(
                                new AccountStatementMail(
                                    $record,
                                    $transactions,
                                    $totalCharges,
                                    $totalPaid,
                                    $balance,
                                    $data['message']
                                )
                            );

                            Notification::make()
                                ->title('Statement Emailed')
                                ->body("The account statement has been sent to {$data['email']}.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Email Failed')
                                ->body("Error: " . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Statement via Email')
                    ->modalDescription('Confirm the recipient email address and add an optional message.')
                    ->modalSubmitActionLabel('Send Email'),
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                Action::make('export_report')
                    ->label('Export Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (\Filament\Resources\Pages\ListRecords $livewire) {
                        return response()->streamDownload(function () use ($livewire) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'Tenant', 
                                'Property', 
                                'Start Date', 
                                'End Date', 
                                'Agreed Rent (RM)', 
                                'Total Charges (RM)', 
                                'Total Paid (RM)', 
                                'Balance (RM)', 
                                'Payment Status', 
                                'Status'
                            ]);

                            // Use the query from Livewire to respect filters and search
                            $records = $livewire->getFilteredTableQuery()->with(['tenant', 'property'])->get();

                            foreach ($records as $record) {
                                // Re-using logic from TenancyAgreementExporter
                                $months = $record->start_date->diffInMonths(now()) + 1;
                                if ($record->end_date && $record->end_date->isPast()) {
                                     $months = $record->start_date->diffInMonths($record->end_date) + 1;
                                }
                                $base = $record->agreed_rent * $months;
                                $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                                $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
                                $lateFees = Invoice::where('tenancy_agreement_id', $record->id)
                                    ->where('type', 'received')
                                    ->sum('late_fee_amount');
                                
                                $totalCharges = $base + $sst + $lateFees;
                                
                                $totalPaid = Invoice::where('tenancy_agreement_id', $record->id)
                                    ->where('type', 'received')
                                    ->where('status', 'paid')
                                    ->sum('amount_total');
                                    
                                $balance = $totalCharges - $totalPaid;
                                
                                $paymentStatus = 'Paid';
                                if ($balance > 0) $paymentStatus = 'Overdue';
                                elseif ($balance < 0) $paymentStatus = 'Advance';

                                fputcsv($handle, [
                                    $record->tenant->name ?? 'N/A',
                                    $record->property->name ?? 'N/A',
                                    $record->start_date?->format('d M Y') ?? 'N/A',
                                    $record->end_date ? $record->end_date->format('d M Y') : 'N/A',
                                    number_format($record->agreed_rent, 2),
                                    number_format($totalCharges, 2),
                                    number_format($totalPaid, 2),
                                    number_format($balance, 2),
                                    $paymentStatus,
                                    $record->is_active ? 'Active' : 'Ended'
                                ]);
                            }

                            fclose($handle);
                        }, 'account-statements-' . now()->format('Y-m-d') . '.csv');
                    }),
                Action::make('consolidated_statement')
                    ->label('Consolidated Statement')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\Select::make('tenant_id')
                            ->label('Select Tenant')
                            ->options(\App\Models\Tenant::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        return response()->streamDownload(function () use ($data) {
                            $tenant = \App\Models\Tenant::find($data['tenant_id']);
                            if (!$tenant) return;

                            $handle = fopen('php://output', 'w');
                            
                            // Header
                            fputcsv($handle, ['Consolidated Statement for ' . $tenant->name]);
                            fputcsv($handle, ['Date: ' . now()->format('d M Y')]);
                            fputcsv($handle, []); // Blank line

                            $grandTotalCharges = 0;
                            $grandTotalPaid = 0;
                            $grandBalance = 0;

                            // Get all agreements
                            $agreements = \App\Models\TenancyAgreement::where('tenant_id', $tenant->id)->get();

                            foreach ($agreements as $agreement) {
                                fputcsv($handle, ['Property: ' . $agreement->property->name . ' (' . $agreement->property->lot_number . ')']);
                                fputcsv($handle, ['Agreement Period: ' . $agreement->start_date->format('d M Y') . ' - ' . $agreement->end_date->format('d M Y')]);
                                fputcsv($handle, ['Date', 'Description', 'Reference', 'Debit (RM)', 'Credit (RM)']);

                                $transactions = AccountOverviewResource::getTransactions($agreement);
                                
                                $subTotalDebit = 0;
                                $subTotalCredit = 0;

                                foreach ($transactions as $t) {
                                    $debit = $t['debit'] ?? 0;
                                    $credit = $t['credit'] ?? 0;
                                    fputcsv($handle, [
                                        $t['date']->format('d M Y'),
                                        $t['description'],
                                        $t['reference'] ?? '',
                                        number_format($debit, 2),
                                        number_format($credit, 2)
                                    ]);
                                    $subTotalDebit += $debit;
                                    $subTotalCredit += $credit;
                                }

                                $balance = $subTotalDebit - $subTotalCredit;
                                fputcsv($handle, ['', '', 'Subtotal', number_format($subTotalDebit, 2), number_format($subTotalCredit, 2)]);
                                fputcsv($handle, ['', '', 'Balance', number_format($balance, 2)]);
                                fputcsv($handle, []); // Blank line

                                $grandTotalCharges += $subTotalDebit;
                                $grandTotalPaid += $subTotalCredit;
                            }

                            $grandBalance = $grandTotalCharges - $grandTotalPaid;
                            fputcsv($handle, ['GRAND TOTAL']);
                            fputcsv($handle, ['Total Charges', number_format($grandTotalCharges, 2)]);
                            fputcsv($handle, ['Total Paid', number_format($grandTotalPaid, 2)]);
                            fputcsv($handle, ['Outstanding Balance', number_format($grandBalance, 2)]);
                            
                            fclose($handle);
                        }, 'consolidated-statement-' . \Illuminate\Support\Str::slug($tenant->name) . '-' . now()->format('Y-m-d') . '.csv');
                    }),
                Action::make('aging_report')
                    ->label('Aging Report')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() || \App\Models\SystemSetting::get('aging_report_enabled', '1') === '1')
                    ->action(function () {
                        return response()->streamDownload(function () {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, ['Aging Report - ' . now()->format('d M Y')]);
                            fputcsv($handle, []);
                            fputcsv($handle, [
                                'Tenant', 
                                'Property', 
                                'Total Outstanding (RM)', 
                                'Current (0-30 Days)', 
                                '31-60 Days', 
                                '61-90 Days', 
                                '> 90 Days'
                            ]);

                            $tenants = \App\Models\Tenant::with(['tenancyAgreements.property', 'invoices' => function($q) {
                                $q->where('type', 'received')
                                  ->where('status', '!=', 'paid')
                                  ->where('status', '!=', 'void');
                            }])->get();

                            foreach ($tenants as $tenant) {
                                // Consolidate all property names
                                $propertyNames = $tenant->tenancyAgreements->map(fn ($ta) => $ta->property->name ?? 'N/A')->unique()->implode(', ');
                                
                                $buckets = [
                                    'total' => 0,
                                    '0-30' => 0,
                                    '31-60' => 0,
                                    '61-90' => 0,
                                    '90+' => 0,
                                ];

                                foreach ($tenant->invoices as $invoice) {
                                    // Outstanding amount logic: amount_total - amount_received
                                    // But schema check: 
                                    // amount_total is usually the "Bill Amount".
                                    // amount_received is "Amount Paid".
                                    // So outstanding = total - received.
                                    
                                    // Wait, in previous logic (e.g. PaymentOffsetService), 
                                    // for pending invoice: amount_received is 0. 
                                    // for partial: amount_received is partial amount.
                                    // But earlier in PaymentOffsetService, we saw:
                                    // $invoice->amount_total = $outstandingAmount - $availableAmount;
                                    // This implies amount_total IS the outstanding balance in some contexts?
                                    
                                    // Let's re-verify specific PaymentOffsetService logic:
                                    // "invoice->amount_total = $outstandingAmount - $availableAmount; // Update remaining balance"
                                    // This suggests `amount_total` effectively becomes the Remaining Balance on the invoice record itself.
                                    
                                    // However, let's verify if `amount_received` is cumulative.
                                    // Yes, we fixed that earlier: $invoice->amount_received += $availableAmount.
                                    
                                    // So, reliable Outstanding Balance = $invoice->amount_total.
                                    // (Since we updated the logic to effectively decrement amount_total as payments constitute).
                                    
                                    $outstanding = $invoice->amount_total;
                                    
                                    // Skip if fully paid (should be status 'paid' anyway but just in case)
                                    if ($outstanding <= 0) continue;
                                    
                                    $ageDays = $invoice->date_received->diffInDays(now(), false);
                                    
                                    // If future invoice (negative age), treat as Current or ignore?
                                    // Pending invoices should be counted.
                                    if ($ageDays <= 30) {
                                        $buckets['0-30'] += $outstanding;
                                    } elseif ($ageDays <= 60) {
                                        $buckets['31-60'] += $outstanding;
                                    } elseif ($ageDays <= 90) {
                                        $buckets['61-90'] += $outstanding;
                                    } else {
                                        $buckets['90+'] += $outstanding;
                                    }
                                    
                                    $buckets['total'] += $outstanding;
                                }

                                // Only show rows with outstanding balance
                                if ($buckets['total'] > 0) {
                                    fputcsv($handle, [
                                        $tenant->name,
                                        $propertyNames ?: 'N/A',
                                        number_format($buckets['total'], 2),
                                        number_format($buckets['0-30'], 2),
                                        number_format($buckets['31-60'], 2),
                                        number_format($buckets['61-90'], 2),
                                        number_format($buckets['90+'], 2),
                                    ]);
                                }
                            }
                            
                            fclose($handle);
                        }, 'aging-report-' . now()->format('Y-m-d') . '.csv');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountOverview::route('/'),
        ];
    }
    
    // Disable create/edit for this overview module
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }

    public static function getTransactions($record)
    {
        $transactions = [];
        
        // 1. Generate Charges (Rent) based on months active
        $currentDate = $record->start_date->copy()->startOfMonth();
        $endDate = $record->end_date && $record->end_date->isPast() 
            ? $record->end_date->copy()->startOfMonth() 
            : now()->startOfMonth();
        
        while ($currentDate <= $endDate) {
            $base = $record->agreed_rent;
            $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
            $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
            $total = $base + $sst;
            
            $transactions[] = [
                'date' => $currentDate->copy(),
                'description' => "Rental for " . $currentDate->format('M Y'),
                'debit' => $total,
                'credit' => 0,
            ];
            
            $currentDate->addMonth();
        }
        
        // 2. Add Payments
        $payments = Invoice::where('tenancy_agreement_id', $record->id)
            ->where('type', 'received')
            ->where('status', 'paid')
            ->get();
            
        foreach ($payments as $payment) {
            $transactions[] = [
                'date' => $payment->date_received ?? $payment->created_at,
                'description' => "Payment Received",
                'reference' => $payment->invoice_number,
                'debit' => 0,
                'credit' => $payment->amount_total,
            ];
        }
        
        // 3. Add Late Fees
        $lateFeeInvoices = Invoice::where('tenancy_agreement_id', $record->id)
            ->where('late_fee_amount', '>', 0)
            ->get();
            
        foreach ($lateFeeInvoices as $inv) {
            $durationStart = $inv->late_fee_duration_start ? $inv->late_fee_duration_start->format('d M Y') : ($inv->date_received ? $inv->date_received->format('d M Y') : 'Unknown');
            
            $transactions[] = [
                'date' => $inv->last_late_fee_applied_at ?? $inv->updated_at,
                'description' => "Late Fee (@ " . ($inv->late_fee_rate_snapshot ?? 0) . "%)" . ($durationStart !== 'Unknown' ? " - Overdue since $durationStart" : ""),
                'debit' => $inv->late_fee_amount,
                'credit' => 0,
                'reference' => "LF-INV-" . $inv->id,
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            if ($a['date']->equalTo($b['date'])) {
                return $b['debit'] <=> $a['debit']; // Charges first
            }
            return $a['date'] <=> $b['date'];
        });
        
        return $transactions;
    }
}
