<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing data
        \Illuminate\Support\Facades\DB::table('invoices')
            ->where('type', 'expenditure')
            ->update(['type' => 'expense']);

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('type')->default('received')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('type')->default('received')->change();
        });

        \Illuminate\Support\Facades\DB::table('invoices')
            ->where('type', 'expense')
            ->update(['type' => 'expenditure']);
    }
};
