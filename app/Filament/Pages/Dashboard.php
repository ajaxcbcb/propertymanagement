<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\RevenueChart::class,
            \App\Filament\Widgets\RecentInvoices::class,
            \App\Filament\Widgets\TopDebtors::class,
        ];
    }
}
