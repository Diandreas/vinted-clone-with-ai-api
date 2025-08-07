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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled', 'refunded', 'disputed'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('product_price', 10, 2);
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->decimal('service_fee', 8, 2)->default(0);
            $table->decimal('seller_earnings', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('dispute_opened_at')->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();
            $table->json('shipping_address');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index('order_number');
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
