<?php
/**
 * Test de redirection de paiement
 * 
 * Ce fichier simule un callback de paiement NotchPay pour tester
 * les redirections vers la page wallet
 */

// Simuler une requête de callback NotchPay
$callbackData = [
    'status' => 'complete', // ou 'failed', 'canceled'
    'reference' => 'test_ref_' . time(),
    'trxref' => 'test_trxref_' . time(),
    'notchpay_trxref' => 'test_notchpay_' . time()
];

// URL de test
$testUrl = 'http://127.0.0.1:8000/payment/callback?' . http_build_query($callbackData);

echo "Test de redirection de paiement\n";
echo "===============================\n\n";

echo "URL de test: " . $testUrl . "\n\n";

echo "Scénarios de test:\n";
echo "1. Status 'complete' -> Redirection vers /wallet avec message de succès\n";
echo "2. Status 'failed' -> Redirection vers /wallet avec message d'erreur\n";
echo "3. Status 'canceled' -> Redirection vers /wallet avec message d'avertissement\n\n";

echo "Pour tester:\n";
echo "1. Assurez-vous que votre serveur Laravel fonctionne sur http://127.0.0.1:8000\n";
echo "2. Visitez l'URL de test dans votre navigateur\n";
echo "3. Vérifiez que vous êtes redirigé vers /wallet avec le bon message\n\n";

echo "Messages attendus:\n";
switch ($callbackData['status']) {
    case 'complete':
        echo "- Succès: 'Paiement réussi ! X jetons ont été ajoutés à votre compte.'\n";
        break;
    case 'failed':
        echo "- Erreur: 'Le paiement a échoué. Veuillez réessayer.'\n";
        break;
    case 'canceled':
        echo "- Avertissement: 'Paiement annulé. Vous pouvez réessayer à tout moment.'\n";
        break;
}

echo "\nNote: Si vous n'avez pas de webhook configuré, utilisez cette URL de test\n";
echo "pour vérifier que les redirections fonctionnent correctement.\n";
