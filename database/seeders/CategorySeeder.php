<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catégories principales
        $categories = [
            [
                'name' => 'Vêtements',
                'slug' => 'vetements',
                'description' => 'Vêtements pour hommes, femmes et enfants',
                'icon' => '👔',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
                'children' => [
                    ['name' => 'Femme', 'description' => 'Vêtements femme'],
                    ['name' => 'Homme', 'description' => 'Vêtements homme'],
                    ['name' => 'Enfant', 'description' => 'Vêtements enfant'],
                ]
            ],
            [
                'name' => 'Chaussures',
                'slug' => 'chaussures',
                'description' => 'Chaussures et accessoires',
                'icon' => '👟',
                'color' => '#EF4444',
                'sort_order' => 2,
                'is_active' => true,
                'children' => [
                    ['name' => 'Baskets', 'description' => 'Baskets et sneakers'],
                    ['name' => 'Talons', 'description' => 'Chaussures à talons'],
                    ['name' => 'Bottes', 'description' => 'Bottes et bottines'],
                ]
            ],
            [
                'name' => 'Sacs',
                'slug' => 'sacs',
                'description' => 'Sacs et maroquinerie',
                'icon' => '👜',
                'color' => '#8B5CF6',
                'sort_order' => 3,
                'is_active' => true,
                'children' => [
                    ['name' => 'Sacs à main', 'description' => 'Sacs à main femme'],
                    ['name' => 'Sacs à dos', 'description' => 'Sacs à dos et cartables'],
                    ['name' => 'Portefeuilles', 'description' => 'Portefeuilles et porte-monnaie'],
                ]
            ],
            [
                'name' => 'Bijoux',
                'slug' => 'bijoux',
                'description' => 'Bijoux et accessoires',
                'icon' => '💎',
                'color' => '#F59E0B',
                'sort_order' => 4,
                'is_active' => true,
                'children' => [
                    ['name' => 'Colliers', 'description' => 'Colliers et pendentifs'],
                    ['name' => "Boucles d'oreilles", 'description' => "Boucles d'oreilles"],
                    ['name' => 'Bracelets', 'description' => 'Bracelets et montres'],
                ]
            ],
            [
                'name' => 'Électronique',
                'slug' => 'electronique',
                'description' => 'Appareils électroniques',
                'icon' => '📱',
                'color' => '#10B981',
                'sort_order' => 5,
                'is_active' => true,
                'children' => [
                    ['name' => 'Téléphones', 'description' => 'Smartphones et accessoires'],
                    ['name' => 'Ordinateurs', 'description' => 'Ordinateurs et tablettes'],
                    ['name' => 'Audio', 'description' => 'Écouteurs et enceintes'],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            // Assurer le slug si manquant
            $categoryData['slug'] = $categoryData['slug'] ?? Str::slug($categoryData['name']);

            $category = Category::create($categoryData);

            // Créer les sous-catégories
            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                $childData['slug'] = $childData['slug'] ?? Str::slug($childData['name']);
                $childData['is_active'] = $childData['is_active'] ?? true;
                $childData['sort_order'] = $childData['sort_order'] ?? 0;

                Category::create($childData);
            }
        }
    }
}
