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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('publishing_package_id')->nullable()->after('user_id')->constrained('product_publishing_packages')->onDelete('set null');
            $table->boolean('requires_publishing_fee')->default(true)->after('publishing_package_id');
            $table->decimal('publishing_fee_paid', 10, 2)->nullable()->after('requires_publishing_fee');
            $table->timestamp('publishing_fee_paid_at')->nullable()->after('publishing_fee_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['publishing_package_id']);
            $table->dropColumn(['publishing_package_id', 'requires_publishing_fee', 'publishing_fee_paid', 'publishing_fee_paid_at']);
        });
    }
};