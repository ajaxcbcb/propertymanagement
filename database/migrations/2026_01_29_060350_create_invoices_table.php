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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenancy_agreement_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('due_date');
            $table->decimal('amount_base', 10, 2);
            $table->decimal('amount_sst', 10, 2)->default(0);
            $table->decimal('amount_total', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'partial', 'overdue'])->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
