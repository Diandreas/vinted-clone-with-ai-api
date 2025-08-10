<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Condition;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            [
                'name' => 'Neuf avec étiquettes',
                'description' => 'Article jamais porté, avec toutes les étiquettes d\'origine',
                'icon' => '🆕',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Neuf sans étiquettes',
                'description' => 'Article jamais porté mais sans les étiquettes d\'origine',
                'icon' => '✨',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Très bon état',
                'description' => 'Article porté quelques fois, aucun défaut visible',
                'icon' => '👌',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Bon état',
                'description' => 'Article porté mais en très bon état, légers signes d\'usage',
                'icon' => '👍',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Satisfaisant',
                'description' => 'Article porté avec quelques défauts mineurs',
                'icon' => '👌',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($conditions as $condition) {
            Condition::create($condition);
        }
    }
}