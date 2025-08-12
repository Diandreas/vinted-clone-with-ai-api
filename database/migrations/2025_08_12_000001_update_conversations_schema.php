<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('conversations', 'buyer_id')) {
                $table->unsignedBigInteger('buyer_id')->after('product_id');
            }
            if (!Schema::hasColumn('conversations', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->after('buyer_id');
            }
            if (!Schema::hasColumn('conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('seller_id');
            }
            if (!Schema::hasColumn('conversations', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('last_message_at');
            }
            if (!Schema::hasColumn('conversations', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add indexes and foreign keys in a separate Schema::table to avoid issues
        Schema::table('conversations', function (Blueprint $table) {
            // Indexes
            $table->index(['buyer_id', 'seller_id'], 'conversations_buyer_seller_index');
            $table->index(['product_id', 'buyer_id', 'seller_id'], 'conversations_product_buyer_seller_index');

            // Foreign keys (guarded by try/catch in case they exist)
            try {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Drop foreign keys if exist
            try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['buyer_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['seller_id']); } catch (\Throwable $e) {}

            // Drop indexes
            try { $table->dropIndex('conversations_buyer_seller_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('conversations_product_buyer_seller_index'); } catch (\Throwable $e) {}

            // Drop columns if exist
            foreach (['product_id','buyer_id','seller_id','last_message_at','is_archived','deleted_at'] as $col) {
                if (Schema::hasColumn('conversations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


