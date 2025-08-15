<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\API\ProductController;

// Simuler une requête
$request = new Request();
$request->merge(['limit' => 30]);

// Créer le contrôleur
$controller = new ProductController();

// Appeler la méthode index
try {
    $response = $controller->index($request);
    $data = $response->getData();
    
    echo "API Response:\n";
    echo "Success: " . ($data->success ? 'true' : 'false') . "\n";
    echo "Total products: " . $data->data->total . "\n";
    echo "Current page: " . $data->data->current_page . "\n";
    echo "Per page: " . $data->data->per_page . "\n";
    echo "Products count: " . count($data->data->data) . "\n";
    
    if (count($data->data->data) > 0) {
        $firstProduct = $data->data->data[0];
        echo "\nFirst product:\n";
        echo "ID: " . $firstProduct->id . "\n";
        echo "Title: " . $firstProduct->title . "\n";
        echo "Main image type: " . gettype($firstProduct->main_image) . "\n";
        echo "Main image value: " . $firstProduct->main_image . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
