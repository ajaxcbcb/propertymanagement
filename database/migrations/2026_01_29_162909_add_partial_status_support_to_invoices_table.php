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
            // Add a column to track the original total before any offsets
            $table->decimal('original_amount_total', 10, 2)->nullable()->after('amount_total');
        });
        
        // Update existing records to store original amount
        DB::statement('UPDATE invoices SET original_amount_total = amount_total WHERE original_amount_total IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('original_amount_total');
        });
    }
};
