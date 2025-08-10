<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Just Do It - Equipementier sportif mondial',
                'logo' => null,
                'website' => 'https://www.nike.com',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Impossible is Nothing - Marque sportive allemande',
                'logo' => null,
                'website' => 'https://www.adidas.com',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Zara',
                'slug' => 'zara',
                'description' => 'Fashion retailer espagnol',
                'logo' => null,
                'website' => 'https://www.zara.com',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'H&M',
                'slug' => 'hm',
                'description' => 'Fast fashion suédois',
                'logo' => null,
                'website' => 'https://www.hm.com',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Louis Vuitton',
                'slug' => 'louis-vuitton',
                'description' => 'Maison de luxe française',
                'logo' => null,
                'website' => 'https://www.louisvuitton.com',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Chanel',
                'slug' => 'chanel',
                'description' => 'Maison de couture parisienne',
                'logo' => null,
                'website' => 'https://www.chanel.com',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Technologie et électronique',
                'logo' => null,
                'website' => 'https://www.apple.com',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Électronique coréenne',
                'logo' => null,
                'website' => 'https://www.samsung.com',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::create($brand);
        }
    }
}