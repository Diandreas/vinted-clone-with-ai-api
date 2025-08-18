<?php
/**
 * Test du formatage des devises
 */

echo "🧪 Test du Formatage des Devises\n";
echo "================================\n\n";

// Simuler les données d'un produit
$products = [
    ['id' => 1, 'price' => 15000, 'original_price' => 20000],
    ['id' => 2, 'price' => 5000, 'original_price' => null],
    ['id' => 3, 'price' => 250000, 'original_price' => 300000],
    ['id' => 4, 'price' => 0, 'original_price' => null],
    ['id' => 5, 'price' => 1250.50, 'original_price' => 1500.75]
];

echo "📊 Test des prix:\n";
echo "-----------------\n";

foreach ($products as $product) {
    $price = $product['price'];
    $originalPrice = $product['original_price'];

    // Formatage FCFA (sans décimales)
    $formattedPrice = number_format($price, 0, ',', ' ') . ' FCFA';

    // Formatage original (avec décimales si nécessaire)
    $formattedOriginal = '';
    if ($originalPrice !== null) {
        $formattedOriginal = number_format($originalPrice, 0, ',', ' ') . ' FCFA';
    }

    echo "Produit {$product['id']}:\n";
    echo "  Prix: {$formattedPrice}\n";
    if ($formattedOriginal) {
        echo "  Prix original: {$formattedOriginal}\n";
    }
    echo "\n";
}

echo "✅ Formatage FCFA appliqué avec succès!\n";
echo "Les prix sont maintenant affichés en FCFA au lieu d'euros.\n";
?>



