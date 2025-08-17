import { CURRENCY_CONFIG, formatCurrency } from '../config/currency.js'

/**
 * Format a price value to Fcfa currency format
 * @param {number} price - The price value to format
 * @returns {string} Formatted price string
 */
export const formatPrice = (price) => {
    return formatCurrency(price)
}

/**
 * Format a price value to a specific currency format
 * @param {number} price - The price value to format
 * @param {string} currency - The currency code (default: 'XAF')
 * @param {string} locale - The locale (default: 'fr-FR')
 * @returns {string} Formatted price string
 */
export const formatPriceCustom = (price, currency = CURRENCY_CONFIG.code, locale = CURRENCY_CONFIG.locale) => {
    if (price === null || price === undefined) return '0 ' + CURRENCY_CONFIG.symbol

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(price)
}

/**
 * Get only the numeric part of a formatted price
 * @param {string} formattedPrice - The formatted price string
 * @returns {number} Numeric price value
 */
export const extractPriceValue = (formattedPrice) => {
    if (!formattedPrice) return 0

    // Remove currency symbols and spaces, replace comma with dot
    const numericString = formattedPrice.replace(/[^\d,.-]/g, '').replace(',', '.')
    return parseFloat(numericString) || 0
}
