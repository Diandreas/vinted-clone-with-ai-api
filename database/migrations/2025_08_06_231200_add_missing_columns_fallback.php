<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users: add stripe_customer_id if missing
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->index();
            }
        });

        // Payment methods: add extended columns if missing
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('payment_methods', 'provider')) {
                $table->string('provider')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'type')) {
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'stripe_payment_method_id')) {
                $table->string('stripe_payment_method_id')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'external_reference')) {
                $table->string('external_reference')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'brand')) {
                $table->string('brand')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'last_four')) {
                $table->string('last_four')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'expires_month')) {
                $table->unsignedTinyInteger('expires_month')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'expires_year')) {
                $table->unsignedSmallInteger('expires_year')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'is_default')) {
                $table->boolean('is_default')->default(false)->index();
            }
            if (!Schema::hasColumn('payment_methods', 'meta')) {
                $table->json('meta')->nullable();
            }
            if (!Schema::hasColumn('payment_methods', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Products: add followers/spot fields if missing
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'followers_only')) {
                $table->boolean('followers_only')->default(false)->index();
            }
            if (!Schema::hasColumn('products', 'is_spot')) {
                $table->boolean('is_spot')->default(false)->index();
            }
            if (!Schema::hasColumn('products', 'spot_starts_at')) {
                $table->timestamp('spot_starts_at')->nullable()->index();
            }
            if (!Schema::hasColumn('products', 'spot_ends_at')) {
                $table->timestamp('spot_ends_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->dropColumn('stripe_customer_id');
            }
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $columns = [
                'user_id', 'provider', 'type', 'stripe_payment_method_id', 'external_reference',
                'brand', 'last_four', 'expires_month', 'expires_year', 'is_default', 'meta'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('payment_methods', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('payment_methods', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $columns = ['followers_only', 'is_spot', 'spot_starts_at', 'spot_ends_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


