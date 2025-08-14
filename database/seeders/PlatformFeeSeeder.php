<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlatformFee;

class PlatformFeeSeeder extends Seeder
{
    public function run(): void
    {
        $fees = [
            [
                'code' => 'listing_fee',
                'name' => 'Listing Fee',
                'type' => 'fixed',
                'amount' => 0.50,
                'percentage' => 0,
                'active' => true,
            ],
            [
                'code' => 'sale_fee',
                'name' => 'Sale Fee',
                'type' => 'percentage',
                'amount' => 0,
                'percentage' => 5.00,
                'active' => true,
            ],
            [
                'code' => 'spot_fee',
                'name' => 'Spot Listing Fee',
                'type' => 'fixed',
                'amount' => 1.50,
                'percentage' => 0,
                'active' => true,
            ],
            [
                'code' => 'boost_fee',
                'name' => 'Boost Fee',
                'type' => 'fixed',
                'amount' => 0.99,
                'percentage' => 0,
                'active' => true,
            ],
        ];

        foreach ($fees as $data) {
            PlatformFee::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}



