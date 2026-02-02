<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Revenue Trend';

    protected function getData(): array
    {
        // Simple 6-month trend of invoice totals
        // In a real app, use flowframe/laravel-trend for creating trend data easily
        
        $months = [];
        $income = [];
        $expense = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Income
            $income[] = \App\Models\Invoice::where('type', 'received')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', ['paid', 'pending'])
                ->sum('amount_total');

            // Expense
            $expense[] = \App\Models\Invoice::where('type', 'expenditure')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'paid')
                ->sum('amount_total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Received',
                    'data' => $income,
                    'fill' => 'start',
                    'borderColor' => '#10b981', // emerald-500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expense,
                    'fill' => 'start',
                    'borderColor' => '#f59e0b', // amber-500
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $months,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}
