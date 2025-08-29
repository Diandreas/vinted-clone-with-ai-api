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
        Schema::table('product_fee_charges', function (Blueprint $table) {
            // Drop old columns that are no longer needed
            if (Schema::hasColumn('product_fee_charges', 'platform_fee_id')) {
                $table->dropForeign(['platform_fee_id']);
                $table->dropColumn('platform_fee_id');
            }
            
            if (Schema::hasColumn('product_fee_charges', 'currency')) {
                $table->dropColumn('currency');
            }
            
            if (Schema::hasColumn('product_fee_charges', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_fee_charges', function (Blueprint $table) {
            // Re-add old columns if needed to rollback
            if (!Schema::hasColumn('product_fee_charges', 'platform_fee_id')) {
                $table->foreignId('platform_fee_id')->constrained('platform_fees')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('product_fee_charges', 'currency')) {
                $table->string('currency', 3)->default('EUR');
            }
            
            if (!Schema::hasColumn('product_fee_charges', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }
};
