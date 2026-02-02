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
            $table->dropColumn('amount_received');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('due_date', 'date_received');
            $table->renameColumn('amount_base', 'amount_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('date_received', 'due_date');
            $table->renameColumn('amount_received', 'amount_base');
            $table->decimal('amount_received', 10, 2)->default(0);
        });
    }
};
