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
        // Vérifier si la table product_fee_charges existe
        if (!Schema::hasTable('product_fee_charges')) {
            Schema::create('product_fee_charges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('fee_id')->constrained('platform_fees')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                $table->timestamps();
                
                $table->index(['product_id', 'fee_id']);
                $table->index(['status']);
            });
        } else {
            // Ajouter les colonnes manquantes si la table existe
            Schema::table('product_fee_charges', function (Blueprint $table) {
                if (!Schema::hasColumn('product_fee_charges', 'fee_id')) {
                    $table->foreignId('fee_id')->constrained('platform_fees')->onDelete('cascade');
                }
                if (!Schema::hasColumn('product_fee_charges', 'amount')) {
                    $table->decimal('amount', 10, 2);
                }
                if (!Schema::hasColumn('product_fee_charges', 'status')) {
                    $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
                }
                if (!Schema::hasColumn('product_fee_charges', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable();
                }
                if (!Schema::hasColumn('product_fee_charges', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
