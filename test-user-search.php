<?php
// Test script for user search endpoint
require_once 'vendor/autoload.php';

// Simulate a request to the user search endpoint
$query = 'nja';
$perPage = 20;

echo "Testing user search endpoint...\n";
echo "Query: {$query}\n";
echo "Per page: {$perPage}\n\n";

// Make a request to the endpoint
$url = "http://127.0.0.1:8000/api/v1/users/search?q={$query}&per_page={$perPage}";
echo "Requesting: {$url}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Status Code: {$httpCode}\n\n";

if (curl_error($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "Response:\n";
    echo $response . "\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "\n✅ SUCCESS: User search endpoint is working!\n";
            if (isset($data['data']['users'])) {
                echo "Found " . count($data['data']['users']) . " users\n";
                foreach ($data['data']['users'] as $user) {
                    echo "- {$user['name']} ({$user['username']})\n";
                }
            }
        } else {
            echo "\n❌ FAILED: API returned success=false\n";
            if (isset($data['message'])) {
                echo "Message: {$data['message']}\n";
            }
        }
    } else {
        echo "\n❌ FAILED: HTTP {$httpCode}\n";
    }
}

curl_close($ch);
echo "\nTest completed.\n";
