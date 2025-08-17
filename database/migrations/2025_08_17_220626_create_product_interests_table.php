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
        Schema::create('product_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['interested', 'negotiating', 'cancelled', 'purchased'])->default('interested');
            $table->decimal('last_offered_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour les requêtes fréquentes
            $table->unique(['product_id', 'user_id'], 'unique_product_user_interest');
            $table->index(['user_id', 'status'], 'user_status_index');
            $table->index(['product_id', 'status'], 'product_status_index');

            // Clés étrangères
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_interests');
    }
};
