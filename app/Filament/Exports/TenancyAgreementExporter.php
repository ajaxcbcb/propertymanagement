<?php

namespace App\Filament\Exports;

use App\Models\TenancyAgreement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TenancyAgreementExporter extends Exporter
{
    protected static ?string $model = TenancyAgreement::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('tenant.name')
                ->label('Tenant'),
            ExportColumn::make('property.name')
                ->label('Property'),
            ExportColumn::make('start_date')
                ->label('Start Date')
                ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y') : ''),
            ExportColumn::make('end_date')
                ->label('End Date')
                ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y') : ''),
            ExportColumn::make('agreed_rent')
                ->label('Agreed Rent'),
            ExportColumn::make('total_rent_due')
                ->label('Total Charges')
                ->state(function (TenancyAgreement $record) {
                    $months = $record->start_date->diffInMonths(now()) + 1;
                    if ($record->end_date && $record->end_date->isPast()) {
                         $months = $record->start_date->diffInMonths($record->end_date) + 1;
                    }
                    
                    $base = $record->agreed_rent * $months;
                    
                    $sstAmount = 0;
                    if ($record->tenant->is_sst_registered) {
                         $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                         $sstAmount = $base * ($sstRate / 100);
                    }
                    
                    $lateFees = $record->invoices()
                         ->where('type', 'received')
                         ->sum('late_fee_amount');
                    
                    return number_format($base + $sstAmount + $lateFees, 2);
                }),
            ExportColumn::make('total_paid')
                ->label('Total Paid')
                ->state(function (TenancyAgreement $record) {
                    $paid = \App\Models\Invoice::where('tenancy_agreement_id', $record->id)
                        ->where('type', 'received')
                        ->where('status', 'paid')
                        ->sum('amount_total');
                    return number_format($paid, 2);
                }),
            ExportColumn::make('balance')
                ->label('Balance')
                ->state(function (TenancyAgreement $record) {
                     $months = $record->start_date->diffInMonths(now()) + 1;
                     if ($record->end_date && $record->end_date->isPast()) {
                          $months = $record->start_date->diffInMonths($record->end_date) + 1;
                     }
                     $base = $record->agreed_rent * $months;
                     $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                     $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
                     $totalLateFees = \App\Models\Invoice::where('tenancy_agreement_id', $record->id)
                         ->where('type', 'received')
                         ->sum('late_fee_amount');
                         
                     $totalCharges = $base + $sst + $totalLateFees;
                     
                     $totalPaid = \App\Models\Invoice::where('tenancy_agreement_id', $record->id)
                         ->where('type', 'received')
                         ->where('status', 'paid')
                         ->sum('amount_total');
                         
                     return number_format($totalCharges - $totalPaid, 2);
                }),
            ExportColumn::make('payment_status')
                ->label('Payment Status')
                ->state(function (TenancyAgreement $record) {
                     $months = $record->start_date->diffInMonths(now()) + 1;
                     if ($record->end_date && $record->end_date->isPast()) {
                          $months = $record->start_date->diffInMonths($record->end_date) + 1;
                     }
                     $base = $record->agreed_rent * $months;
                     $sstRate = \App\Models\SystemSetting::get('sst_rate', 8);
                     $sst = $record->tenant->is_sst_registered ? ($base * ($sstRate / 100)) : 0;
                     $totalLateFees = \App\Models\Invoice::where('tenancy_agreement_id', $record->id)
                         ->where('type', 'received')
                         ->sum('late_fee_amount');
                         
                     $totalCharges = $base + $sst + $totalLateFees;
                     
                     $totalPaid = \App\Models\Invoice::where('tenancy_agreement_id', $record->id)
                         ->where('type', 'received')
                         ->where('status', 'paid')
                         ->sum('amount_total');
                         
                     $balance = $totalCharges - $totalPaid;
                     
                     if ($balance > 0) return 'Overdue';
                     if ($balance < 0) return 'Advance Payment';
                     return 'Paid';
                }),
            ExportColumn::make('is_active')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Ended'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your tenancy agreement export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
