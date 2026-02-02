<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SystemSetting extends Model
{
    use LogsActivity;

    protected $fillable = ['key', 'label', 'value', 'type', 'description'];

    public static function get(string $key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget("system_setting_{$setting->key}");
        });

        static::deleted(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget("system_setting_{$setting->key}");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
