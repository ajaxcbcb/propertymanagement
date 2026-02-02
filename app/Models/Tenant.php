<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tenant extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_sst_registered',
        'sst_registration_date',
        'wallet_balance',
    ];

    protected $casts = [
        'is_sst_registered' => 'boolean',
        'sst_registration_date' => 'date',
        'wallet_balance' => 'decimal:2',
    ];

    public function tenancyAgreements()
    {
        return $this->hasMany(TenancyAgreement::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
