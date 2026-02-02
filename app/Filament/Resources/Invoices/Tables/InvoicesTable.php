<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'received' => 'success',
                        'expenditure' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'received' => 'Received',
                        'expenditure' => 'Expenses',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('property.name')
                    ->label('Property')
                    ->state(function ($record) {
                        return $record->property?->name ?? $record->tenancyAgreement?->property?->name ?? '-';
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->label('Receipt No.')
                    ->searchable(),
                TextColumn::make('date_received')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount_received')
                    ->label('Amount')
                    ->money('MYR')
                    ->sortable(),
                TextColumn::make('amount_sst')
                    ->label('SST')
                    ->money('MYR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount_total')
                    ->label('Total (Net)')
                    ->money('MYR')
                    ->sortable(),
                // Late fee columns removed as requested
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'void' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('export_sst_report')
                    ->label('Export SST Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        DatePicker::make('month')
                            ->label('Select Month')
                            ->format('Y-m')
                            ->displayFormat('F Y')
                            ->required()
                            ->default(now()),
                    ])
                    ->action(function (array $data) {
                        $date = \Carbon\Carbon::parse($data['month']);
                        $start = $date->startOfMonth()->format('Y-m-d');
                        $end = $date->endOfMonth()->format('Y-m-d');
                        
                        $invoices = \App\Models\Invoice::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                            ->where('type', 'received')
                            ->where('amount_sst', '>', 0)
                            ->with('tenant')
                            ->get();
                            
                        $callback = function () use ($invoices) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Receipt No.', 'Date Received', 'Tenant Name', 'SST Reg No', 'Amount Received', 'SST Amount', 'Total (Net)']);
                            
                            foreach ($invoices as $invoice) {
                                fputcsv($file, [
                                    $invoice->invoice_number,
                                    $invoice->date_received ? $invoice->date_received->format('Y-m-d') : 'N/A',
                                    $invoice->tenant->name,
                                    $invoice->tenant->sst_registration_date ?? 'N/A',
                                    $invoice->amount_received,
                                    $invoice->amount_sst,
                                    $invoice->amount_total,
                                ]);
                            }
                            fclose($file);
                        };
                        
                        return response()->stream($callback, 200, [
                            "Content-type" => "text/csv",
                            "Content-Disposition" => "attachment; filename=sst-report-{$date->format('Y-m')}.csv",
                            "Pragma" => "no-cache",
                            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                            "Expires" => "0"
                        ]);
                    })
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'received' => 'Received',
                        'expenditure' => 'Expenses',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('property_id')
                    ->label('Property')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('date_received', 'desc')
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
