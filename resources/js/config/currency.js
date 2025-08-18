/**
 * Configuration de devise pour l'application Vinted Clone
 */

export const CURRENCY_CONFIG = {
    // Code de devise ISO 4217
    code: 'XAF',

    // Symbole de la devise
    symbol: 'Fcfa',

    // Nom complet de la devise
    name: 'Franc CFA',

    // Configuration de formatage
    locale: 'fr-FR',

    // Nombre de décimales
    decimals: 2,

    // Formatage pour l'affichage
    format: {
        style: 'currency',
        currency: 'XAF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }
}

// Fonction utilitaire pour formater les prix selon la configuration
export const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '0 Fcfa'

    return new Intl.NumberFormat(CURRENCY_CONFIG.locale, CURRENCY_CONFIG.format).format(amount)
}

// Fonction pour obtenir juste le symbole
export const getCurrencySymbol = () => CURRENCY_CONFIG.symbol

// Fonction pour obtenir le code de devise
export const getCurrencyCode = () => CURRENCY_CONFIG.code
