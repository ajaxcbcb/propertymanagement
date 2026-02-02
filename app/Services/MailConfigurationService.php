<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;

class MailConfigurationService
{
    public static function apply(): void
    {
        $enabled = SystemSetting::get('mail_enabled', '0') === '1';
        
        if (!$enabled) {
            Config::set('mail.default', 'log');
            return;
        }

        $config = [
            'transport' => 'smtp',
            'host' => SystemSetting::get('mail_host', config('mail.mailers.smtp.host')),
            'port' => SystemSetting::get('mail_port', config('mail.mailers.smtp.port')),
            'encryption' => SystemSetting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'username' => SystemSetting::get('mail_username', config('mail.mailers.smtp.username')),
            'password' => SystemSetting::get('mail_password', config('mail.mailers.smtp.password')),
            'timeout' => null,
            'auth_mode' => null,
        ];

        Config::set('mail.mailers.smtp', array_merge(config('mail.mailers.smtp'), $config));
        Config::set('mail.from.address', SystemSetting::get('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', SystemSetting::get('mail_from_name', config('mail.from.name')));
        
        Config::set('mail.default', 'smtp');
    }
}
