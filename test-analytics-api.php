<?php
/**
 * Test de l'API Analytics
 * Usage: php test-analytics-api.php
 */

echo "🧪 Test de l'API Analytics\n";
echo "==========================\n\n";

$baseUrl = 'http://127.0.0.1:8000';

// Test 1: Dashboard Analytics
echo "1️⃣ Test Dashboard Analytics:\n";
echo "-----------------------------\n";

$dashboardUrl = $baseUrl . '/api/v1/analytics/dashboard';
echo "URL: {$dashboardUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $dashboardUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Dashboard Analytics fonctionne\n";
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "  - Données reçues avec succès\n";
    } else {
        echo "  - Réponse reçue mais format incorrect\n";
    }
} else {
    echo "❌ Erreur Dashboard Analytics\n";
    echo "  Réponse: {$response}\n";
}

echo "\n";

// Test 2: Products Analytics
echo "2️⃣ Test Products Analytics:\n";
echo "----------------------------\n";

$productsUrl = $baseUrl . '/api/v1/analytics/products';
echo "URL: {$productsUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $productsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Products Analytics fonctionne\n";
} else {
    echo "❌ Erreur Products Analytics\n";
    echo "  Réponse: {$response}\n";
}

echo "\n";

// Test 3: Sales Analytics
echo "3️⃣ Test Sales Analytics:\n";
echo "-------------------------\n";

$salesUrl = $baseUrl . '/api/v1/analytics/sales';
echo "URL: {$salesUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $salesUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Sales Analytics fonctionne\n";
} else {
    echo "❌ Erreur Sales Analytics\n";
    echo "  Réponse: {$response}\n";
}

echo "\n";

// Test 4: Followers Analytics
echo "4️⃣ Test Followers Analytics:\n";
echo "------------------------------\n";

$followersUrl = $baseUrl . '/api/v1/analytics/followers';
echo "URL: {$followersUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $followersUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: {$httpCode}\n";

if ($httpCode === 200) {
    echo "✅ Followers Analytics fonctionne\n";
} else {
    echo "❌ Erreur Followers Analytics\n";
    echo "  Réponse: {$response}\n";
}

echo "\n";

echo "🏁 Test terminé\n";
echo "Si vous obtenez des erreurs 401, c'est normal (authentification requise)\n";
echo "Si vous obtenez des erreurs 500, il y a encore des problèmes à résoudre\n";
?>



