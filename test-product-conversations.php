<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;

echo "🔧 TEST PRODUCT CONVERSATIONS\n";
echo "=============================\n\n";

// Find a product owner
$productOwner = User::whereHas('products')->first();
if (!$productOwner) {
    echo "❌ Aucun utilisateur avec des produits trouvé\n";
    exit(1);
}

// Find a product of this owner
$product = $productOwner->products()->first();
if (!$product) {
    echo "❌ Aucun produit trouvé\n";
    exit(1);
}

echo "👤 Propriétaire: {$productOwner->name} (ID: {$productOwner->id})\n";
echo "📦 Produit: {$product->title} (ID: {$product->id})\n\n";

// Generate token for the product owner
$token = $productOwner->createToken('test-product-conversations')->plainTextToken;

// Test the endpoint
$url = "http://localhost:8000/api/v1/conversations/product/{$product->id}/conversations";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);

echo "📡 Appel GET $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Status: $httpCode\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ SUCCESS!\n\n";
        
        $conversations = $data['data']['conversations'] ?? [];
        $totalCount = $data['data']['total_conversations'] ?? 0;
        $unreadCount = $data['data']['unread_count'] ?? 0;
        
        echo "📊 Résultats:\n";
        echo "   🔸 Conversations totales: $totalCount\n";
        echo "   🔸 Messages non lus: $unreadCount\n";
        echo "   🔸 Conversations dans la liste: " . count($conversations) . "\n\n";
        
        if (count($conversations) > 0) {
            echo "📝 Liste des conversations:\n";
            foreach ($conversations as $conv) {
                echo "   - Conv #{$conv['id']}\n";
                echo "     🔸 Acheteur: {$conv['buyer']['name']}\n";
                echo "     🔸 Dernière activité: {$conv['last_message_at']}\n";
                if (isset($conv['last_message'])) {
                    $content = substr($conv['last_message']['content'], 0, 50);
                    echo "     🔸 Dernier message: \"$content...\"\n";
                }
                echo "\n";
            }
        } else {
            echo "ℹ️ Aucune conversation pour ce produit\n";
        }
    } else {
        echo "❌ Réponse invalide\n";
        echo $response . "\n";
    }
} else {
    echo "❌ Erreur HTTP $httpCode\n";
    echo $response . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
if ($httpCode === 200) {
    echo "✅ L'endpoint fonctionne! Le compteur peut maintenant être corrigé.\n";
} else {
    echo "❌ L'endpoint ne fonctionne pas.\n";
}
echo "\n";