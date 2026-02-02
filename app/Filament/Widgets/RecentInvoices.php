<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentInvoices extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Recent Invoices';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Invoice::query()->latest()->limit(5)
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('type')
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
                    }),
                \Filament\Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Receipt #')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant / Property')
                    ->state(function($record) {
                        return $record->tenant?->name ?? $record->property?->name ?? '-';
                    }),
                \Filament\Tables\Columns\TextColumn::make('amount_total')
                    ->label('Total')
                    ->money('MYR')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'void' => 'gray',
                        default => 'warning',
                    }),
                \Filament\Tables\Columns\TextColumn::make('date_received')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->paginated(false); // Hide pagination for widget
    }
}
