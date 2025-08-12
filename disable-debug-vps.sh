#!/bin/bash

echo "🔧 Désactivation du debug en production..."

# Vérifier qu'on est bien sur le VPS
if [[ "$(hostname)" == *"srv560445"* ]]; then
    echo "✅ VPS détecté, désactivation en cours..."
else
    echo "⚠️  Attention : Ce script est destiné au VPS uniquement !"
    exit 1
fi

# Désactiver le debug dans .env
echo "📝 Modification du fichier .env..."
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env
sed -i 's/TELESCOPE_ENABLED=true/TELESCOPE_ENABLED=false/g' .env

# Désactiver Telescope
echo "🔍 Désactivation de Telescope..."
php artisan telescope:disable

# Vider tous les caches
echo "🧹 Vidage des caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear

# Vérifier la configuration
echo "✅ Configuration finale :"
echo "   - APP_DEBUG: $(grep APP_DEBUG .env | cut -d'=' -f2)"
echo "   - TELESCOPE_ENABLED: $(grep TELESCOPE_ENABLED .env | cut -d'=' -f2)"

echo ""
echo "🎯 Debug désactivé ! La barre de debug ne devrait plus apparaître."
echo "🔄 Redémarrez votre navigateur si nécessaire."


