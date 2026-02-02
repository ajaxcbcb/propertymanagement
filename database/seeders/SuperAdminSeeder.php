<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@propertymanagement.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Ideally change this in production
                'role' => 'super_admin',
            ]
        );
        
        $this->command->info('Super Admin user created: superadmin@propertymanagement.com / password');
    }
}
