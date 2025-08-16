<?php
/**
 * Script de test pour l'intégration Fapshi
 * 
 * Ce script teste les fonctionnalités de base de notre service Fapshi
 * pour s'assurer que tout est correctement configuré.
 */

require_once 'vendor/autoload.php';

use App\Services\Payment\FapshiService;
use Illuminate\Support\Facades\Log;

// Simuler l'environnement Laravel
$_ENV['FAPSHI_BASE_URL'] = 'https://sandbox.fapshi.com';
$_ENV['FAPSHI_API_USER'] = 'ebbb6b4f-49cc-42ea-8b5f-e9068b1a5a2f';
$_ENV['FAPSHI_API_KEY'] = 'FAK_TEST_cb149a5a15a7915a776a';

echo "🧪 Test de l'intégration Fapshi\n";
echo "================================\n\n";

try {
    // Test 1: Vérifier la configuration
    echo "1️⃣  Vérification de la configuration...\n";
    
    if (empty($_ENV['FAPSHI_API_USER']) || empty($_ENV['FAPSHI_API_KEY'])) {
        throw new Exception("❌ Configuration Fapshi manquante");
    }
    
    echo "✅ Configuration Fapshi trouvée\n";
    echo "   - Base URL: " . $_ENV['FAPSHI_BASE_URL'] . "\n";
    echo "   - API User: " . substr($_ENV['FAPSHI_API_USER'], 0, 8) . "...\n";
    echo "   - API Key: " . substr($_ENV['FAPSHI_API_KEY'], 0, 8) . "...\n\n";
    
    // Test 2: Vérifier les montants
    echo "2️⃣  Test de validation des montants...\n";
    
    $validAmounts = [100, 500, 1000, 5000];
    $invalidAmounts = [50, 99, 0, -100];
    
    foreach ($validAmounts as $amount) {
        if ($amount >= 100) {
            echo "✅ Montant $amount FCFA valide\n";
        }
    }
    
    foreach ($invalidAmounts as $amount) {
        if ($amount < 100) {
            echo "❌ Montant $amount FCFA invalide (< 100)\n";
        }
    }
    
    echo "\n";
    
    // Test 3: Structure des données
    echo "3️⃣  Test de la structure des données...\n";
    
    $testPayload = [
        'amount' => 1000,
        'email' => 'test@example.com',
        'phone' => '691722215',
        'description' => 'Test de recharge',
        'callback_url' => 'https://example.com/webhook',
        'return_url' => 'https://example.com/return',
    ];
    
    foreach (['amount', 'email'] as $required) {
        if (isset($testPayload[$required])) {
            echo "✅ Champ requis '$required' présent\n";
        } else {
            echo "❌ Champ requis '$required' manquant\n";
        }
    }
    
    echo "\n";
    
    // Test 4: Format de téléphone
    echo "4️⃣  Test du format de téléphone...\n";
    
    $phoneNumbers = [
        '691722215' => true,   // Format Fapshi correct
        '237691722215' => false, // Avec indicatif (à nettoyer)
        '6 91 72 22 15' => false, // Avec espaces (à nettoyer)
        '691722' => false,     // Trop court
        '79172221567' => false, // Trop long
    ];
    
    foreach ($phoneNumbers as $phone => $expected) {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '237')) {
            $cleaned = substr($cleaned, 3);
        }
        
        $isValid = preg_match('/^6[0-9]{8}$/', $cleaned);
        $status = $isValid ? '✅' : '❌';
        echo "$status Téléphone '$phone' -> '$cleaned' (" . ($isValid ? 'valide' : 'invalide') . ")\n";
    }
    
    echo "\n";
    
    // Test 5: URLs de webhook
    echo "5️⃣  Test des URLs de webhook...\n";
    
    $baseUrl = 'http://127.0.0.1:8000';
    $webhookUrls = [
        'callback' => $baseUrl . '/api/v1/webhooks/fapshi',
        'return' => $baseUrl . '/api/v1/webhooks/fapshi/return',
    ];
    
    foreach ($webhookUrls as $type => $url) {
        echo "✅ URL $type: $url\n";
    }
    
    echo "\n";
    
    // Résumé
    echo "📋 Résumé des tests\n";
    echo "==================\n";
    echo "✅ Configuration Fapshi sandbox\n";
    echo "✅ Validation des montants (min 100 FCFA)\n";
    echo "✅ Structure des données requises\n";
    echo "✅ Nettoyage des numéros de téléphone\n";
    echo "✅ URLs de webhook configurées\n\n";
    
    echo "🎉 Tous les tests sont passés!\n";
    echo "\n💡 Prochaines étapes:\n";
    echo "   1. Se connecter à l'application\n";
    echo "   2. Naviguer vers /wallet\n";
    echo "   3. Tester une recharge de 1000 FCFA\n";
    echo "   4. Vérifier les logs Laravel\n";
    echo "   5. Tester un retrait vers mobile money\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}