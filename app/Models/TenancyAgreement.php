<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TenancyAgreement extends Model
{
    use LogsActivity;

    protected static function booted()
    {
        static::saving(function ($tenancyAgreement) {
            $now = now();
            if ($tenancyAgreement->start_date && $tenancyAgreement->end_date) {
                // Determine if the agreement is currently active based on the date range
                $tenancyAgreement->is_active = $now->betweenIncluded($tenancyAgreement->start_date, $tenancyAgreement->end_date);
            }
        });
    }
    protected $fillable = [
        'tenant_id',
        'property_id',
        'start_date',
        'end_date',
        'agreed_rent',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'agreed_rent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
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
