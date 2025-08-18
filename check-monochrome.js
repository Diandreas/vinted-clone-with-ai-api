#!/usr/bin/env node

/**
 * Script de vérification du thème monochrome total
 * Vérifie qu'aucune couleur non monochrome n'est utilisée dans l'application
 */

const fs = require('fs');
const path = require('path');

// Couleurs autorisées (monochrome)
const allowedColors = [
    // Tons de vert (primary)
    'primary-50', 'primary-100', 'primary-200', 'primary-300', 'primary-400',
    'primary-500', 'primary-600', 'primary-700', 'primary-800', 'primary-900',

    // Tons de gris
    'gray-50', 'gray-100', 'gray-200', 'gray-300', 'gray-400',
    'gray-500', 'gray-600', 'gray-700', 'gray-800', 'gray-900',

    // Couleurs monochromes d'accent
    'success', 'warning', 'error', 'info',

    // Couleurs neutres
    'neutral-50', 'neutral-100', 'neutral-200', 'neutral-300', 'neutral-400',
    'neutral-500', 'neutral-600', 'neutral-700', 'neutral-800', 'neutral-900',

    // Couleurs de base autorisées
    'white', 'black', 'transparent', 'current'
];

// Couleurs interdites (non monochromes)
const forbiddenColors = [
    'red', 'yellow', 'blue', 'purple', 'pink', 'orange', 'indigo', 'teal', 'cyan',
    'emerald', 'lime', 'amber', 'rose', 'violet', 'fuchsia', 'sky', 'slate'
];

// Patterns à rechercher
const colorPatterns = [
    // Classes Tailwind
    /(bg|text|border|ring|shadow|from|to|via)-(red|yellow|blue|purple|pink|orange|indigo|teal|cyan|emerald|lime|amber|rose|violet|fuchsia|sky|slate)-\d+/g,

    // Couleurs hexadécimales
    /#[0-9a-fA-F]{6}/g,

    // Couleurs CSS
    /(red|yellow|blue|purple|pink|orange|indigo|teal|cyan|emerald|lime|amber|rose|violet|fuchsia|sky|slate)/gi,

    // Classes personnalisées avec couleurs interdites
    /rikeaa.*(red|yellow|blue|purple|pink|orange|indigo|teal|cyan|emerald|lime|amber|rose|violet|fuchsia|sky|slate)/gi
];

// Extensions de fichiers à vérifier
const fileExtensions = ['.vue', '.js', '.jsx', '.ts', '.tsx', '.css', '.scss'];

// Fonction pour scanner un fichier
function scanFile(filePath) {
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        const issues = [];

        colorPatterns.forEach(pattern => {
            const matches = content.match(pattern);
            if (matches) {
                matches.forEach(match => {
                    // Vérifier si la couleur est autorisée
                    const isAllowed = allowedColors.some(allowed =>
                        match.toLowerCase().includes(allowed.toLowerCase())
                    );

                    if (!isAllowed) {
                        issues.push({
                            type: 'Couleur interdite',
                            match: match,
                            line: findLineNumber(content, match)
                        });
                    }
                });
            }
        });

        return issues;
    } catch (error) {
        return [{
            type: 'Erreur de lecture',
            match: error.message,
            line: 'N/A'
        }];
    }
}

// Fonction pour trouver le numéro de ligne
function findLineNumber(content, searchText) {
    const lines = content.split('\n');
    for (let i = 0; i < lines.length; i++) {
        if (lines[i].includes(searchText)) {
            return i + 1;
        }
    }
    return 'N/A';
}

// Fonction pour scanner un répertoire
function scanDirectory(dirPath, results = []) {
    const items = fs.readdirSync(dirPath);

    items.forEach(item => {
        const fullPath = path.join(dirPath, item);
        const stat = fs.statSync(fullPath);

        if (stat.isDirectory()) {
            // Ignorer les dossiers node_modules et .git
            if (!['node_modules', '.git', 'vendor', 'storage'].includes(item)) {
                scanDirectory(fullPath, results);
            }
        } else if (stat.isFile()) {
            const ext = path.extname(item);
            if (fileExtensions.includes(ext)) {
                const issues = scanFile(fullPath);
                if (issues.length > 0) {
                    results.push({
                        file: fullPath,
                        issues: issues
                    });
                }
            }
        }
    });

    return results;
}

// Fonction principale
function main() {
    console.log('🔍 Vérification du thème monochrome total...\n');

    const startTime = Date.now();
    const results = scanDirectory('./resources/js');
    const endTime = Date.now();

    console.log(`⏱️  Scan terminé en ${endTime - startTime}ms\n`);

    if (results.length === 0) {
        console.log('✅ Aucune couleur non monochrome détectée !');
        console.log('🎨 L\'application respecte parfaitement le thème monochrome total.');
    } else {
        console.log(`❌ ${results.length} fichier(s) avec des couleurs non monochromes détecté(s) :\n`);

        results.forEach(result => {
            console.log(`📁 ${result.file}`);
            result.issues.forEach(issue => {
                console.log(`   ${issue.type}: "${issue.match}" (ligne ${issue.line})`);
            });
            console.log('');
        });

        console.log('💡 Recommandations :');
        console.log('   - Remplacer les couleurs rouges par gray-600');
        console.log('   - Remplacer les couleurs jaunes par gray-500');
        console.log('   - Remplacer les couleurs bleues par primary-500');
        console.log('   - Utiliser uniquement la palette monochrome verte et grise');

        process.exit(1);
    }
}

// Exécuter le script
if (require.main === module) {
    main();
}

module.exports = { scanDirectory, scanFile, allowedColors, forbiddenColors };
