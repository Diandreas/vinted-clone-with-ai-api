#!/bin/bash

echo "🚀 Build pour VPS en cours..."

# Nettoyer les builds précédents
echo "🧹 Nettoyage des builds précédents..."
rm -rf public/build
rm -rf public/hot

# Installer les dépendances
echo "📦 Installation des dépendances..."
npm install

# Build de production
echo "🔨 Build de production..."
npm run build

# Optimisations pour le VPS
echo "⚡ Optimisations pour le VPS..."

# Vérifier que le build s'est bien passé
if [ -d "public/build" ]; then
    echo "✅ Build réussi !"
    echo "📁 Contenu du dossier build :"
    ls -la public/build/
    
    echo ""
    echo "🔧 Configuration détectée :"
    echo "   - Environment: Production"
    echo "   - API Base URL: /api/v1"
    echo "   - Build optimisé pour VPS"
    
    echo ""
    echo "📋 Prochaines étapes :"
    echo "   1. Uploader le dossier 'public/build' sur votre VPS"
    echo "   2. Vérifier que l'URL de base est correcte"
    echo "   3. Tester l'affichage des produits"
    
else
    echo "❌ Erreur lors du build !"
    exit 1
fi
