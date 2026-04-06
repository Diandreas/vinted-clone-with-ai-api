<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_status')->default('none'); // none|pending|verified|rejected
            $table->string('kyc_document_type')->nullable(); // cni|passport
            $table->string('kyc_document_path')->nullable();
            $table->string('kyc_selfie_path')->nullable();
            $table->timestamp('kyc_verified_at')->nullable();
            $table->string('kyc_rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_document_type',
                'kyc_document_path',
                'kyc_selfie_path',
                'kyc_verified_at',
                'kyc_rejection_reason',
            ]);
        });
    }
};
