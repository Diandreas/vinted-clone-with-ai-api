<?php

require_once __DIR__ . '/vendor/autoload.php';

// Test rapide des fonctions de devise
echo "=== Test des fonctions de devise FCFA ===\n";

// Test de formatage de prix
echo "Format price 1000: " . format_price(1000) . "\n";
echo "Format price 1500.50: " . format_price(1500.50) . "\n";
echo "Format price 0: " . format_price(0) . "\n";
echo "Format price null: " . format_price(null) . "\n";

// Test des symboles
echo "Currency symbol: " . get_currency_symbol() . "\n";
echo "Currency code: " . get_currency_code() . "\n";

echo "=== Test terminé ===\n";
