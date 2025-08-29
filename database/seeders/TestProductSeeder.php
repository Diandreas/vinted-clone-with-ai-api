<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;

class TestProductSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer l'utilisateur de test
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $this->command->error('Utilisateur de test non trouvé. Exécutez d\'abord TestUserSeeder.');
            return;
        }

        // Récupérer ou créer des données de base
        $category = Category::firstOrCreate(['name' => 'Vêtements'], ['name' => 'Vêtements']);
        $brand = Brand::firstOrCreate(['name' => 'Test Brand'], ['name' => 'Test Brand']);
        $condition = Condition::firstOrCreate(['name' => 'Neuf'], ['name' => 'Neuf']);

        // Créer un produit de test
        $product = Product::updateOrCreate(
            ['title' => 'Produit de test pour NotchPay'],
            [
                'user_id' => $user->id,
                'title' => 'Produit de test pour NotchPay',
                'description' => 'Ce produit est créé pour tester le système de paiement NotchPay',
                'price' => 10000, // 10 000 FCFA
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'condition_id' => $condition->id,
                'status' => 'pending_payment', // Statut en attente de paiement
                'location' => 'Douala, Cameroun',
                'is_spot' => false,
                'followers_only' => false,
            ]
        );

        $this->command->info("Produit de test créé: {$product->title} (ID: {$product->id})");
        $this->command->info("Prix: {$product->price} FCFA");
        $this->command->info("Statut: {$product->status}");
        $this->command->info("Frais de publication: " . round($product->price * 0.05) . " FCFA");
    }
}
