<?php

require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

try {
    echo "Creating Google Vision client...\n";
    
    // Test if the class exists
    if (!class_exists(ImageAnnotatorClient::class)) {
        echo "Class ImageAnnotatorClient not found!\n";
        exit(1);
    }
    
    echo "Class exists, creating client...\n";
    
    $client = new ImageAnnotatorClient([
        'credentials' => 'D:\\project\\vinted\\vinted-clone-with-ai-api\\storage\\credentials\\google-cloud-key.json',
    ]);
    
    echo "Client created successfully!\n";
    echo "Google Vision API is properly configured for production.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}