<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_fees')->updateOrInsert(
            ['code' => 'listing_fee'],
            [
                'name' => 'Listing Fee',
                'type' => 'fixed',
                'amount' => 100.00,
                'percentage' => 0,
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('platform_fees')
            ->where('code', 'listing_fee')
            ->update([
                'type' => 'fixed',
                'amount' => 500.00,
                'percentage' => 0,
                'updated_at' => now(),
            ]);
    }
};
