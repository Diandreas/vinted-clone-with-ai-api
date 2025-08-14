<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_fees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // listing_fee, sale_fee, spot_fee, boost_fee
            $table->string('name');
            $table->enum('type', ['fixed', 'percentage']);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->json('meta')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_fee_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('platform_fee_id')->constrained('platform_fees');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_fee_charges');
        Schema::dropIfExists('platform_fees');
    }
};


