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
        Schema::create('product_publishing_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('package_id')->unique(); // Unique package identifier
            $table->integer('product_count'); // Number of products in the package
            $table->decimal('estimated_total_value', 15, 2)->nullable(); // Estimated total value of products
            $table->decimal('total_fee', 10, 2); // Total publishing fee
            $table->json('fee_breakdown'); // Detailed fee calculation
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->string('payment_gateway_id')->nullable(); // Lygos payment gateway ID
            $table->string('payment_link')->nullable(); // Payment URL
            $table->timestamp('expires_at'); // When the package payment expires
            $table->timestamp('paid_at')->nullable();
            $table->json('product_slots')->nullable(); // Reserved slots for products
            $table->integer('used_slots')->default(0); // Number of slots used
            $table->json('payment_details')->nullable(); // Payment gateway response details
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_publishing_packages');
    }
};