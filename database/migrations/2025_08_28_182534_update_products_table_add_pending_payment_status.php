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
        // Modify the products table to update status enum to include 'pending_payment'
        Schema::table('products', function (Blueprint $table) {
            // Update the status column enum to include the new status
            $table->enum('status', ['draft', 'pending_payment', 'active', 'sold', 'reserved', 'removed'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert to the original enum values
            $table->enum('status', ['draft', 'active', 'sold', 'reserved', 'removed'])->change();
        });
    }
};
