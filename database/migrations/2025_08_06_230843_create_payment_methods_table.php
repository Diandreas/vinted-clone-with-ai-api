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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider')->nullable(); // stripe, mtn_momo, orange_money
            $table->string('type')->nullable(); // card, momo, paypal, etc.
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('external_reference')->nullable(); // for MoMo, etc.
            $table->string('brand')->nullable();
            $table->string('last_four')->nullable();
            $table->unsignedTinyInteger('expires_month')->nullable();
            $table->unsignedSmallInteger('expires_year')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
