<?php
/**
 * Script de test pour vérifier la fonction extractMessageContent
 */

echo "🧪 Test de la fonction extractMessageContent\n";
echo "========================================\n\n";

// Simuler la fonction JavaScript extractMessageContent
function extractMessageContent($content, $maxLength = null) {
    if (empty($content)) return '';
    
    $messageText = $content;
    
    // Essayer de parser le JSON si c'est une chaîne JSON
    try {
        $parsed = json_decode($content, true);
        if ($parsed && is_array($parsed)) {
            // Si c'est un objet avec une propriété 'content', l'utiliser
            if (isset($parsed['content']) && is_string($parsed['content'])) {
                $messageText = $parsed['content'];
            }
            // Si c'est un objet avec une propriété 'text', l'utiliser
            elseif (isset($parsed['text']) && is_string($parsed['text'])) {
                $messageText = $parsed['text'];
            }
            // Si c'est un objet avec une propriété 'message', l'utiliser
            elseif (isset($parsed['message']) && is_string($parsed['message'])) {
                $messageText = $parsed['message'];
            }
            // Sinon, essayer de convertir l'objet en chaîne lisible
            else {
                $messageText = json_encode($parsed, JSON_PRETTY_PRINT);
            }
        }
    } catch (Exception $e) {
        // Si ce n'est pas du JSON valide, utiliser le contenu tel quel
        $messageText = $content;
    }
    
    // Appliquer la limite de longueur si spécifiée
    if ($maxLength && strlen($messageText) > $maxLength) {
        $messageText = substr($messageText, 0, $maxLength) . '...';
    }
    
    return $messageText;
}

// Tests
$testCases = [
    // Messages JSON
    '{"content":"Salut Linkea"}' => 'Salut Linkea',
    '{"text":"Comment ça va?"}' => 'Comment ça va?',
    '{"message":"Bonjour"}' => 'Bonjour',
    '{"data":"Test","type":"info"}' => '{"data":"Test","type":"info"}',
    
    // Messages simples
    'Message simple' => 'Message simple',
    'Bonjour tout le monde !' => 'Bonjour tout le monde !',
    
    // Messages vides ou null
    '' => '',
    'null' => 'null',
    
    // Messages avec caractères spéciaux
    '{"content":"C\'est un test avec des caractères spéciaux : éàçù"}' => 'C\'est un test avec des caractères spéciaux : éàçù',
    '{"text":"Prix: 1000 FCFA"}' => 'Prix: 1000 FCFA'
];

echo "1️⃣ Tests de base...\n";
echo "==================\n";

$allTestsPassed = true;

foreach ($testCases as $input => $expected) {
    $result = extractMessageContent($input);
    $passed = ($result === $expected);
    
    if ($passed) {
        echo "   ✅ ";
    } else {
        echo "   ❌ ";
        $allTestsPassed = false;
    }
    
    echo "Input: " . (strlen($input) > 50 ? substr($input, 0, 50) . '...' : $input) . "\n";
    echo "      Expected: " . $expected . "\n";
    echo "      Got:      " . $result . "\n";
    echo "\n";
}

echo "2️⃣ Tests avec limitation de longueur...\n";
echo "=====================================\n";

$lengthTests = [
    ['{"content":"Message très long qui devrait être tronqué"}', 20, 'Message très long...'],
    ['{"text":"Court"}', 10, 'Court'],
    ['Message simple', 5, 'Messa...']
];

foreach ($lengthTests as $test) {
    list($input, $maxLength, $expected) = $test;
    $result = extractMessageContent($input, $maxLength);
    $passed = ($result === $expected);
    
    if ($passed) {
        echo "   ✅ ";
    } else {
        echo "   ❌ ";
        $allTestsPassed = false;
    }
    
    echo "Input: " . $input . " (max: $maxLength)\n";
    echo "      Expected: " . $expected . "\n";
    echo "      Got:      " . $result . "\n";
    echo "\n";
}

echo "3️⃣ Tests de performance...\n";
echo "========================\n";

$startTime = microtime(true);
$iterations = 10000;

for ($i = 0; $i < $iterations; $i++) {
    extractMessageContent('{"content":"Test de performance"}');
}

$endTime = microtime(true);
$duration = ($endTime - $startTime) * 1000; // en millisecondes

echo "   ⏱️  $iterations itérations en " . number_format($duration, 2) . " ms\n";
echo "   📊 " . number_format($iterations / ($duration / 1000), 0) . " opérations/seconde\n";

echo "\n🎯 Résumé des tests :\n";
echo "===================\n";

if ($allTestsPassed) {
    echo "✅ TOUS LES TESTS SONT PASSÉS !\n";
    echo "🎉 La fonction extractMessageContent fonctionne parfaitement.\n";
} else {
    echo "❌ CERTAINS TESTS ONT ÉCHOUÉ !\n";
    echo "🔧 Vérifiez la logique de la fonction.\n";
}

echo "\n📝 Utilisation dans vos composants :\n";
echo "1. Importez la fonction : import { extractMessageContent } from '@/utils/messageUtils'\n";
echo "2. Utilisez-la : {{ extractMessageContent(message.content) }}\n";
echo "3. Avec limitation : {{ extractMessageContent(message.content, 30) }}\n";

echo "\n🧪 Pour tester dans le navigateur :\n";
echo "// Dans la console du navigateur\n";
echo "console.log(extractMessageContent('{\"content\":\"Salut Linkea\"}'));\n";
echo "// Devrait afficher : Salut Linkea\n";
?>
