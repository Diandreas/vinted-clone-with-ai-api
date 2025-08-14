<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet_balance_xaf')) {
                $table->unsignedBigInteger('wallet_balance_xaf')->default(0)->after('privacy_settings');
                $table->index('wallet_balance_xaf');
            }
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('purpose', ['topup', 'order_payment', 'refund', 'payout'])->index();
            $table->unsignedBigInteger('amount_xaf');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->string('provider')->nullable()->index();
            $table->string('trans_id')->nullable()->index();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wallet_balance_xaf')) {
                $table->dropColumn('wallet_balance_xaf');
            }
        });
    }
};


