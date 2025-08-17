<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de devise
    |--------------------------------------------------------------------------
    |
    | Configuration globale de la devise utilisée dans l'application
    |
    */

    'code' => env('CURRENCY_CODE', 'XAF'),
    'symbol' => env('CURRENCY_SYMBOL', 'Fcfa'),
    'name' => env('CURRENCY_NAME', 'Franc CFA'),
    'locale' => env('CURRENCY_LOCALE', 'fr_FR'),
    'decimals' => env('CURRENCY_DECIMALS', 2),

    /*
    |--------------------------------------------------------------------------
    | Formatage des prix
    |--------------------------------------------------------------------------
    |
    | Configuration pour le formatage des prix dans l'application
    |
    */

    'formatting' => [
        'decimal_separator' => ',',
        'thousands_separator' => ' ',
        'symbol_position' => 'after', // 'before' ou 'after'
    ],
];
