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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('sst_rate_snapshot', 5, 2)->default(0)->nullable()->after('amount_sst')->comment('Percentage rate used for GST calculation');
            $table->decimal('late_fee_rate_snapshot', 5, 2)->default(0)->nullable()->after('late_fee_amount')->comment('Percentage rate used for Late Fee calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
