<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('value');
            $table->string('type')->default('string'); // string, number, boolean, percent
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed initial values
        DB::table('system_settings')->insert([
            [
                'key' => 'sst_rate',
                'label' => 'SST Rate (%)',
                'value' => '8',
                'type' => 'percent',
                'description' => 'Sales and Service Tax rate applied to registered tenants.',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key' => 'late_fee_rate',
                'label' => 'Late Fee Monthly Rate (%)',
                'value' => '2',
                'type' => 'percent',
                'description' => 'Monthly penalty rate for overdue invoices.',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key' => 'late_fee_grace_period',
                'label' => 'Late Fee Grace Period (Days)',
                'value' => '7',
                'type' => 'number',
                'description' => 'Days after due date before late fee starts accumulating.',
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
