<?php

namespace App\Providers;


use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\TenancyAgreement;
use App\Observers\InvoiceObserver;
use App\Observers\TenantObserver;
use App\Observers\TenancyAgreementObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }


        Invoice::observe(InvoiceObserver::class);
        Tenant::observe(TenantObserver::class);
        TenancyAgreement::observe(TenancyAgreementObserver::class);
    }
}
