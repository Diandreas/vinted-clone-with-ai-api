<?php
/**
 * Script de test pour l'API de like
 * Usage: php test-like-api.php
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$productId = 1; // ID du produit à tester

echo "🧪 Test de l'API de Like\n";
echo "========================\n";
echo "Base URL: {$baseUrl}\n";
echo "Produit ID: {$productId}\n\n";

// Test 1: Vérifier que la route existe (sans authentification)
echo "1️⃣ Test de la route (sans authentification):\n";
$url = "{$baseUrl}/api/v1/products/{$productId}/like";
echo "URL: {$url}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "Code HTTP: {$httpCode}\n";
echo "Réponse attendue: 401 Unauthorized (pas de token)\n";
echo "Réponse reçue: {$httpCode}\n";

if ($httpCode === 401) {
    echo "✅ Route accessible - Authentification requise\n";
} else {
    echo "❌ Problème avec la route\n";
}

echo "\n";

// Test 2: Vérifier la structure de la réponse d'erreur
echo "2️⃣ Structure de la réponse d'erreur:\n";
echo "Headers:\n";
$headerLines = explode("\n", $headers);
foreach ($headerLines as $line) {
    if (trim($line)) {
        echo "  " . trim($line) . "\n";
    }
}

echo "\nBody:\n";
if ($body) {
    $jsonData = json_decode($body, true);
    if ($jsonData) {
        echo "  " . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "  " . $body . "\n";
    }
} else {
    echo "  (vide)\n";
}

echo "\n";

// Test 3: Vérifier les routes disponibles
echo "3️⃣ Vérification des routes API:\n";
$routesUrl = "{$baseUrl}/api/v1";
echo "URL: {$routesUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $routesUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Code HTTP: {$httpCode}\n";
if ($response) {
    echo "Réponse: {$response}\n";
} else {
    echo "Réponse: (vide)\n";
}

echo "\n";

// Test 4: Vérifier la configuration Laravel
echo "4️⃣ Vérification de la configuration:\n";
echo "Vérifiez que:\n";
echo "  - Laravel est démarré (php artisan serve)\n";
echo "  - Les routes sont chargées (php artisan route:list)\n";
echo "  - Le middleware auth:sanctum est configuré\n";
echo "  - La base de données est accessible\n";

echo "\n";

// Test 5: Commandes artisan utiles
echo "5️⃣ Commandes artisan utiles:\n";
echo "  php artisan route:list --path=api/v1/products\n";
echo "  php artisan route:list --name=products.like\n";
echo "  php artisan config:cache\n";
echo "  php artisan route:cache\n";

echo "\n";

// Test 6: Vérifier le ProductController
echo "6️⃣ Vérification du ProductController:\n";
echo "  - Méthode 'like' existe dans app/Http/Controllers/API/ProductController.php\n";
echo "  - Modèle Product a la méthode 'toggleLike'\n";
echo "  - Middleware auth:sanctum est appliqué\n";

echo "\n";

curl_close($ch);

echo "🏁 Test terminé\n";
echo "Si vous obtenez 401, c'est normal (authentification requise)\n";
echo "Si vous obtenez 404, la route n'existe pas\n";
echo "Si vous obtenez 405, la méthode HTTP n'est pas autorisée\n";
echo "Si vous obtenez 500, il y a une erreur serveur\n";
?>


