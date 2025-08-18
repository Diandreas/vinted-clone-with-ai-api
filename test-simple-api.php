<?php
/**
 * Test simple de l'API
 */

$url = 'http://127.0.0.1:8000/api/v1/products/1/like';

echo "Testing: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";

if ($httpCode === 401) {
    echo "✅ SUCCESS: Route accessible, authentication required\n";
} elseif ($httpCode === 302) {
    echo "❌ PROBLEM: Route redirected (caught by web routes)\n";
} elseif ($httpCode === 404) {
    echo "❌ PROBLEM: Route not found\n";
} elseif ($httpCode === 405) {
    echo "❌ PROBLEM: Method not allowed\n";
} else {
    echo "❓ UNKNOWN: Unexpected response\n";
}

curl_close($ch);
?>


