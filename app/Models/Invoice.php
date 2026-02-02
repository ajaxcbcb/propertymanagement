<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use LogsActivity;

    protected $fillable = [
        'type',
        'tenant_id',
        'tenancy_agreement_id',
        'property_id',
        'invoice_number',
        'date_received',
        'amount_received',
        'amount_sst',
        'amount_total',
        'original_amount_total',
        'sst_rate_snapshot',
        'late_fee_amount',
        'late_fee_rate_snapshot',
        'last_late_fee_applied_at',
        'late_fee_duration_start',
        'status',
        'description',
    ];

    protected $casts = [
        'date_received' => 'date',
        'amount_received' => 'decimal:2',
        'amount_sst' => 'decimal:2',
        'amount_total' => 'decimal:2',
        'original_amount_total' => 'decimal:2',
        'sst_rate_snapshot' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'late_fee_rate_snapshot' => 'decimal:2',
        'last_late_fee_applied_at' => 'datetime',
        'late_fee_duration_start' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenancyAgreement()
    {
        return $this->belongsTo(TenancyAgreement::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
