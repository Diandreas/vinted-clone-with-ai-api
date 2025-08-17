<?php

if (!function_exists('format_price')) {
    /**
     * Formate un prix selon la configuration de devise
     * 
     * @param float|int $price
     * @return string
     */
    function format_price($price)
    {
        if ($price === null || $price === '') {
            return '0 ' . config('currency.symbol', 'Fcfa');
        }

        $decimals = config('currency.decimals', 2);
        $decimalSeparator = config('currency.formatting.decimal_separator', ',');
        $thousandsSeparator = config('currency.formatting.thousands_separator', ' ');
        $symbol = config('currency.symbol', 'Fcfa');
        $symbolPosition = config('currency.formatting.symbol_position', 'after');

        $formattedPrice = number_format($price, $decimals, $decimalSeparator, $thousandsSeparator);

        if ($symbolPosition === 'before') {
            return $symbol . ' ' . $formattedPrice;
        }

        return $formattedPrice . ' ' . $symbol;
    }
}

if (!function_exists('get_currency_symbol')) {
    /**
     * Retourne le symbole de devise configuré
     * 
     * @return string
     */
    function get_currency_symbol()
    {
        return config('currency.symbol', 'Fcfa');
    }
}

if (!function_exists('get_currency_code')) {
    /**
     * Retourne le code de devise configuré
     * 
     * @return string
     */
    function get_currency_code()
    {
        return config('currency.code', 'XAF');
    }
}
