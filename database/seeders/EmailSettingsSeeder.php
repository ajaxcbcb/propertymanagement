<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class EmailSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'mail_enabled',
                'label' => 'Enable Email Feature',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Global toggle to enable or disable email sending features.',
            ],
            [
                'key' => 'mail_host',
                'label' => 'SMTP Host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'description' => 'The hostname of your SMTP server.',
            ],
            [
                'key' => 'mail_port',
                'label' => 'SMTP Port',
                'value' => '2525',
                'type' => 'number',
                'description' => 'The port used for SMTP connections.',
            ],
            [
                'key' => 'mail_username',
                'label' => 'SMTP Username',
                'value' => '',
                'type' => 'string',
                'description' => 'The username for SMTP authentication.',
            ],
            [
                'key' => 'mail_password',
                'label' => 'SMTP Password',
                'value' => '',
                'type' => 'password',
                'description' => 'The password for SMTP authentication.',
            ],
            [
                'key' => 'mail_encryption',
                'label' => 'SMTP Encryption',
                'value' => 'tls',
                'type' => 'string',
                'description' => 'The encryption protocol (tls, ssl, or null).',
            ],
            [
                'key' => 'mail_from_address',
                'label' => 'Mail From Address',
                'value' => 'noreply@propmaster.com',
                'type' => 'string',
                'description' => 'The email address that will appear in the "From" field.',
            ],
            [
                'key' => 'mail_from_name',
                'label' => 'Mail From Name',
                'value' => 'PropMaster Management',
                'type' => 'string',
                'description' => 'The name that will appear in the "From" field.',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
