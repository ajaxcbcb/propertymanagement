<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // 1. Total Monthly Rent (Potential)
        // Sum of agreed_rent from all active tenancy agreements
        $totalMonthlyRent = \App\Models\TenancyAgreement::where('is_active', true)->sum('agreed_rent');

        // 2. This Month's Potential Outstanding
        // Based on active tenancy agreements (expected rent to collect this month)
        $potentialOutstanding = \App\Models\TenancyAgreement::where('is_active', true)
            ->sum('agreed_rent');
        
        // Subtract what's already been paid this month
        $paidThisMonth = \App\Models\Invoice::where('type', 'received')
            ->where('status', 'paid')
            ->whereYear('date_received', now()->year)
            ->whereMonth('date_received', now()->month)
            ->sum('amount_total');
        
        $outstanding = $potentialOutstanding - $paidThisMonth;

        // 3. Last Month's Expenses
        $lastMonthExpenses = \App\Models\Invoice::where('type', 'expenditure')
            ->where('status', 'paid')
            ->whereYear('date_received', now()->subMonth()->year)
            ->whereMonth('date_received', now()->subMonth()->month)
            ->sum('amount_total');

        // 3. Occupancy Rate
        $totalProperties = \App\Models\Property::count();
        $occupiedProperties = \App\Models\Property::where('status', 'occupied')->count();
        $occupancyRate = $totalProperties > 0 ? ($occupiedProperties / $totalProperties) * 100 : 0;

        return [
            Stat::make('Total Monthly Rent', 'RM ' . number_format($totalMonthlyRent, 2))
                ->description('Potential income')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Last Month\'s Expenses', 'RM ' . number_format($lastMonthExpenses, 2))
                ->description(now()->subMonth()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make('This Month\'s Outstanding', 'RM ' . number_format($outstanding, 2))
                ->description(now()->format('F Y') . ' (after payments)')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('Occupancy Rate', number_format($occupancyRate, 0) . '%')
                ->description($occupiedProperties . ' / ' . $totalProperties . ' occupied')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
