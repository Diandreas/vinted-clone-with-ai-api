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
            if (!Schema::hasColumn('products', 'followers_only')) {
                $table->boolean('followers_only')->default(false)->after('status');
            }
            if (!Schema::hasColumn('products', 'is_spot')) {
                $table->boolean('is_spot')->default(false)->after('followers_only');
            }
            if (!Schema::hasColumn('products', 'spot_starts_at')) {
                $table->timestamp('spot_starts_at')->nullable()->after('is_spot');
            }
            if (!Schema::hasColumn('products', 'spot_ends_at')) {
                $table->timestamp('spot_ends_at')->nullable()->after('spot_starts_at');
            }

            $table->index(['followers_only']);
            $table->index(['is_spot', 'spot_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'followers_only')) {
                $table->dropColumn('followers_only');
            }
            if (Schema::hasColumn('products', 'is_spot')) {
                $table->dropColumn('is_spot');
            }
            if (Schema::hasColumn('products', 'spot_starts_at')) {
                $table->dropColumn('spot_starts_at');
            }
            if (Schema::hasColumn('products', 'spot_ends_at')) {
                $table->dropColumn('spot_ends_at');
            }
        });
    }
};


