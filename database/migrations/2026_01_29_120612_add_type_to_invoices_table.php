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
            $table->string('type')->default('received')->after('id');
            $table->foreignId('property_id')->nullable()->after('tenancy_agreement_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->unsignedBigInteger('tenancy_agreement_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn(['type', 'property_id']);
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->unsignedBigInteger('tenancy_agreement_id')->nullable(false)->change();
        });
    }
};
