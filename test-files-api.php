<?php
/**
 * Test de l'API des fichiers
 */

$baseUrl = 'http://127.0.0.1:8000';
$imagePath = 'products/1755447670_1_0.png';

echo "🧪 Test de l'API des Fichiers\n";
echo "==============================\n";
echo "Base URL: {$baseUrl}\n";
echo "Image Path: {$imagePath}\n\n";

// Test 1: Route API des fichiers
echo "1️⃣ Test de la route API des fichiers:\n";
$url = "{$baseUrl}/api/v1/files/{$imagePath}";
echo "URL: {$url}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Fichier accessible via l'API\n";
} elseif ($httpCode === 404) {
    echo "❌ Fichier non trouvé\n";
} else {
    echo "❓ Réponse inattendue\n";
}

echo "\n";

// Test 2: Route web des fichiers (ancienne méthode)
echo "2️⃣ Test de la route web des fichiers (ancienne):\n";
$url = "{$baseUrl}/storage/{$imagePath}";
echo "URL: {$url}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Fichier accessible via web/storage\n";
} elseif ($httpCode === 404) {
    echo "❌ Fichier non trouvé via web/storage\n";
} else {
    echo "❓ Réponse inattendue\n";
}

echo "\n";

// Test 3: Vérification de la structure des dossiers
echo "3️⃣ Vérification de la structure:\n";
echo "Vérifiez que:\n";
echo "  - Le dossier storage/app/public/products existe\n";
echo "  - Le fichier 1755449334_1_0.png existe dans ce dossier\n";
echo "  - Le lien symbolique storage:link est créé\n\n";

// Test 4: Commandes artisan utiles
echo "4️⃣ Commandes artisan utiles:\n";
echo "  php artisan storage:link\n";
echo "  ls -la storage/app/public/products/\n";
echo "  ls -la public/storage/\n\n";

// Test 5: Vérification des permissions
echo "5️⃣ Vérification des permissions:\n";
echo "  - Dossier storage: 755\n";
echo "  - Fichiers images: 644\n";
echo "  - Propriétaire: www-data ou utilisateur du serveur\n\n";

curl_close($ch);

echo "🏁 Test terminé\n";
echo "Si vous obtenez 404, vérifiez:\n";
echo "  1. Le fichier existe dans storage/app/public/products/\n";
echo "  2. Le lien symbolique est créé: php artisan storage:link\n";
echo "  3. Les permissions sont correctes\n";
echo "  4. La route API est bien ajoutée dans routes/api.php\n";
?>
