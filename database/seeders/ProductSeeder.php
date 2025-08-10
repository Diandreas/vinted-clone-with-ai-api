<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Condition;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $conditions = Condition::all();
        
        if ($users->isEmpty() || $categories->isEmpty() || $conditions->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter les seeders User, Category et Condition');
            return;
        }

        $products = [
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->where('name', 'Vêtements')->first()?->id ?? $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Robe d\'été fleurie Zara',
                'description' => 'Magnifique robe d\'été avec motifs floraux, portée seulement une fois. Parfait pour les beaux jours !',
                'price' => 25.50,
                'original_price' => 39.99,
                'size' => 'M',
                'color' => 'Bleu fleuri',
                'material' => 'Viscose',
                'status' => 'active',
                'location' => 'Paris',
                'is_negotiable' => true,
                'tags' => ['été', 'fleuri', 'zara', 'robe'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->where('name', 'Chaussures')->first()?->id ?? $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Baskets Nike Air Force 1',
                'description' => 'Baskets blanches classiques, peu portées. Quelques marques de port normales.',
                'price' => 60.00,
                'original_price' => 110.00,
                'size' => '42',
                'color' => 'Blanc',
                'material' => 'Cuir',
                'status' => 'active',
                'location' => 'Lyon',
                'is_negotiable' => false,
                'tags' => ['nike', 'baskets', 'blanc', 'classique'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->where('name', 'Sacs')->first()?->id ?? $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Sac à main Louis Vuitton',
                'description' => 'Sac à main authentique en très bon état, avec certificat d\'authenticité.',
                'price' => 450.00,
                'original_price' => 800.00,
                'color' => 'Marron',
                'material' => 'Cuir',
                'status' => 'active',
                'location' => 'Marseille',
                'is_negotiable' => true,
                'tags' => ['louis vuitton', 'luxe', 'authentique', 'sac'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->where('name', 'Bijoux')->first()?->id ?? $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Montre Daniel Wellington',
                'description' => 'Montre élégante avec bracelet en cuir, fonctionne parfaitement.',
                'price' => 80.00,
                'original_price' => 150.00,
                'color' => 'Or rose',
                'material' => 'Acier inoxydable',
                'status' => 'active',
                'location' => 'Toulouse',
                'is_negotiable' => true,
                'tags' => ['montre', 'daniel wellington', 'élégant'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->where('name', 'Électronique')->first()?->id ?? $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'iPhone 12 64GB',
                'description' => 'iPhone en excellent état, toujours avec protection écran et coque. Batterie à 89%.',
                'price' => 350.00,
                'original_price' => 699.00,
                'color' => 'Noir',
                'status' => 'active',
                'location' => 'Bordeaux',
                'is_negotiable' => true,
                'tags' => ['iphone', 'apple', 'smartphone'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Jean Levi\'s 501',
                'description' => 'Jean classique en excellent état, taille parfaitement.',
                'price' => 35.00,
                'original_price' => 90.00,
                'size' => '32/34',
                'color' => 'Bleu délavé',
                'material' => 'Denim',
                'status' => 'draft',
                'location' => 'Nice',
                'is_negotiable' => true,
                'tags' => ['jean', 'levis', 'classique', 'denim'],
            ],
            [
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'condition_id' => $conditions->random()->id,
                'title' => 'Veste en cuir vintage',
                'description' => 'Veste en cuir véritable style motard, quelques marques de caractère.',
                'price' => 120.00,
                'size' => 'L',
                'color' => 'Noir',
                'material' => 'Cuir véritable',
                'status' => 'sold',
                'location' => 'Lille',
                'is_negotiable' => false,
                'tags' => ['cuir', 'vintage', 'motard', 'veste'],
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
        
        $this->command->info('Produits créés avec succès !');
    }
}