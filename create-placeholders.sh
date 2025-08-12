#!/bin/bash

echo "🎨 Création des images de placeholder..."

# Créer le dossier images s'il n'existe pas
mkdir -p public/images

# Créer une image de placeholder pour les produits (SVG simple)
cat > public/placeholder-product.jpg << 'EOF'
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="400" fill="#f3f4f6"/>
  <text x="200" y="200" font-family="Arial, sans-serif" font-size="24" fill="#9ca3af" text-anchor="middle" dy=".3em">
    Image non disponible
  </text>
</svg>
EOF

# Créer une image de placeholder pour les avatars (SVG simple)
cat > public/default-avatar.png << 'EOF'
<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
  <circle cx="50" cy="50" r="50" fill="#e5e7eb"/>
  <circle cx="50" cy="35" r="15" fill="#9ca3af"/>
  <path d="M 20 80 Q 50 60 80 80" stroke="#9ca3af" stroke-width="3" fill="none"/>
</svg>
EOF

echo "✅ Images de placeholder créées !"
echo "📁 Fichiers créés :"
echo "   - public/placeholder-product.jpg"
echo "   - public/default-avatar.png"
echo ""
echo "⚠️  Note: Ces sont des fichiers SVG avec l'extension .jpg/.png"
echo "   Pour de vraies images, remplacez-les par des fichiers image réels"

