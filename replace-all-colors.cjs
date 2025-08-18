#!/usr/bin/env node

/**
 * Script pour remplacer TOUTES les couleurs non monochromes
 * par des couleurs monochromes (vert et gris uniquement)
 */

const fs = require('fs');
const path = require('path');

// Mappings de couleurs : anciennes → nouvelles (monochromes)
const colorMappings = {
  // Rouge → Gris
  'bg-red-50': 'bg-gray-50',
  'bg-red-100': 'bg-gray-100',
  'bg-red-200': 'bg-gray-200',
  'bg-red-300': 'bg-gray-300',
  'bg-red-400': 'bg-gray-400',
  'bg-red-500': 'bg-gray-600',
  'bg-red-600': 'bg-gray-700',
  'bg-red-700': 'bg-gray-800',
  'bg-red-800': 'bg-gray-900',
  'bg-red-900': 'bg-gray-900',
  
  'text-red-50': 'text-gray-50',
  'text-red-100': 'text-gray-100',
  'text-red-200': 'text-gray-200',
  'text-red-300': 'text-gray-300',
  'text-red-400': 'text-gray-400',
  'text-red-500': 'text-gray-600',
  'text-red-600': 'text-gray-700',
  'text-red-700': 'text-gray-800',
  'text-red-800': 'text-gray-900',
  'text-red-900': 'text-gray-900',
  
  'border-red-50': 'border-gray-50',
  'border-red-100': 'border-gray-100',
  'border-red-200': 'border-gray-200',
  'border-red-300': 'border-gray-300',
  'border-red-400': 'border-gray-400',
  'border-red-500': 'border-gray-600',
  'border-red-600': 'border-gray-700',
  'border-red-700': 'border-gray-800',
  'border-red-800': 'border-gray-900',
  'border-red-900': 'border-gray-900',
  
  'ring-red-50': 'ring-gray-50',
  'ring-red-100': 'ring-gray-100',
  'ring-red-200': 'ring-gray-200',
  'ring-red-300': 'ring-gray-300',
  'ring-red-400': 'ring-gray-400',
  'ring-red-500': 'ring-gray-600',
  'ring-red-600': 'ring-gray-700',
  'ring-red-700': 'ring-gray-800',
  'ring-red-800': 'ring-gray-900',
  'ring-red-900': 'ring-gray-900',
  
  // Jaune → Gris
  'bg-yellow-50': 'bg-gray-50',
  'bg-yellow-100': 'bg-gray-100',
  'bg-yellow-200': 'bg-gray-200',
  'bg-yellow-300': 'bg-gray-300',
  'bg-yellow-400': 'bg-gray-400',
  'bg-yellow-500': 'bg-gray-500',
  'bg-yellow-600': 'bg-gray-600',
  'bg-yellow-700': 'bg-gray-700',
  'bg-yellow-800': 'bg-gray-800',
  'bg-yellow-900': 'bg-gray-900',
  
  'text-yellow-50': 'text-gray-50',
  'text-yellow-100': 'text-gray-100',
  'text-yellow-200': 'text-gray-200',
  'text-yellow-300': 'text-gray-300',
  'text-yellow-400': 'text-gray-400',
  'text-yellow-500': 'text-gray-500',
  'text-yellow-600': 'text-gray-600',
  'text-yellow-700': 'text-gray-700',
  'text-yellow-800': 'text-gray-800',
  'text-yellow-900': 'text-gray-900',
  
  'border-yellow-50': 'border-gray-50',
  'border-yellow-100': 'border-gray-100',
  'border-yellow-200': 'border-gray-200',
  'border-yellow-300': 'border-gray-300',
  'border-yellow-400': 'border-gray-400',
  'border-yellow-500': 'border-gray-500',
  'border-yellow-600': 'border-gray-600',
  'border-yellow-700': 'border-gray-700',
  'border-yellow-800': 'border-gray-800',
  'border-yellow-900': 'border-gray-900',
  
  'ring-yellow-50': 'ring-gray-50',
  'ring-yellow-100': 'ring-gray-100',
  'ring-yellow-200': 'ring-gray-200',
  'ring-yellow-300': 'ring-gray-300',
  'ring-yellow-400': 'ring-gray-400',
  'ring-yellow-500': 'ring-gray-500',
  'ring-yellow-600': 'ring-gray-600',
  'ring-yellow-700': 'ring-gray-700',
  'ring-yellow-800': 'ring-gray-800',
  'ring-yellow-900': 'ring-gray-900',
  
  // Bleu → Vert (primary)
  'bg-blue-50': 'bg-primary-50',
  'bg-blue-100': 'bg-primary-100',
  'bg-blue-200': 'bg-primary-200',
  'bg-blue-300': 'bg-primary-300',
  'bg-blue-400': 'bg-primary-400',
  'bg-blue-500': 'bg-primary-500',
  'bg-blue-600': 'bg-primary-600',
  'bg-blue-700': 'bg-primary-700',
  'bg-blue-800': 'bg-primary-800',
  'bg-blue-900': 'bg-primary-900',
  
  'text-blue-50': 'text-primary-50',
  'text-blue-100': 'text-primary-100',
  'text-blue-200': 'text-primary-200',
  'text-blue-300': 'text-primary-300',
  'text-blue-400': 'text-primary-400',
  'text-blue-500': 'text-primary-500',
  'text-blue-600': 'text-primary-600',
  'text-blue-700': 'text-primary-700',
  'text-blue-800': 'text-primary-800',
  'text-blue-900': 'text-primary-900',
  
  'border-blue-50': 'border-primary-50',
  'border-blue-100': 'border-primary-100',
  'border-blue-200': 'border-primary-200',
  'border-blue-300': 'border-primary-300',
  'border-blue-400': 'border-primary-400',
  'border-blue-500': 'border-primary-500',
  'border-blue-600': 'border-primary-600',
  'border-blue-700': 'border-primary-700',
  'border-blue-800': 'border-primary-800',
  'border-blue-900': 'border-primary-900',
  
  'ring-blue-50': 'ring-primary-50',
  'ring-blue-100': 'ring-primary-100',
  'ring-blue-200': 'ring-primary-200',
  'ring-blue-300': 'ring-primary-300',
  'ring-blue-400': 'ring-primary-400',
  'ring-blue-500': 'ring-primary-500',
  'ring-blue-600': 'ring-primary-600',
  'ring-blue-700': 'ring-primary-700',
  'ring-blue-800': 'ring-primary-800',
  'ring-blue-900': 'ring-primary-900',
  
  // Indigo → Vert (primary)
  'bg-indigo-50': 'bg-primary-50',
  'bg-indigo-100': 'bg-primary-100',
  'bg-indigo-200': 'bg-primary-200',
  'bg-indigo-300': 'bg-primary-300',
  'bg-indigo-400': 'bg-primary-400',
  'bg-indigo-500': 'bg-primary-500',
  'bg-indigo-600': 'bg-primary-600',
  'bg-indigo-700': 'bg-primary-700',
  'bg-indigo-800': 'bg-primary-800',
  'bg-indigo-900': 'bg-primary-900',
  
  'text-indigo-50': 'text-primary-50',
  'text-indigo-100': 'text-primary-100',
  'text-indigo-200': 'text-primary-200',
  'text-indigo-300': 'text-primary-300',
  'text-indigo-400': 'text-primary-400',
  'text-indigo-500': 'text-primary-500',
  'text-indigo-600': 'text-primary-600',
  'text-indigo-700': 'text-primary-700',
  'text-indigo-800': 'text-primary-800',
  'text-indigo-900': 'text-primary-900',
  
  'border-indigo-50': 'border-primary-50',
  'border-indigo-100': 'border-primary-100',
  'border-indigo-200': 'border-primary-200',
  'border-indigo-300': 'border-primary-300',
  'border-indigo-400': 'border-primary-400',
  'border-indigo-500': 'border-primary-500',
  'border-indigo-600': 'border-primary-600',
  'border-indigo-700': 'border-primary-700',
  'border-indigo-800': 'border-primary-800',
  'border-indigo-900': 'border-primary-900',
  
  'ring-indigo-50': 'ring-primary-50',
  'ring-indigo-100': 'ring-primary-100',
  'ring-indigo-200': 'ring-primary-200',
  'ring-indigo-300': 'ring-primary-300',
  'ring-indigo-400': 'ring-primary-400',
  'ring-indigo-500': 'ring-primary-500',
  'ring-indigo-600': 'ring-primary-600',
  'ring-indigo-700': 'ring-primary-700',
  'ring-indigo-800': 'ring-primary-800',
  'ring-indigo-900': 'ring-primary-900',
  
  // Violet/Purple → Vert (primary)
  'bg-violet-50': 'bg-primary-50',
  'bg-violet-100': 'bg-primary-100',
  'bg-violet-200': 'bg-primary-200',
  'bg-violet-300': 'bg-primary-300',
  'bg-violet-400': 'bg-primary-400',
  'bg-violet-500': 'bg-primary-500',
  'bg-violet-600': 'bg-primary-600',
  'bg-violet-700': 'bg-primary-700',
  'bg-violet-800': 'bg-primary-800',
  'bg-violet-900': 'bg-primary-900',
  
  'bg-purple-50': 'bg-primary-50',
  'bg-purple-100': 'bg-primary-100',
  'bg-purple-200': 'bg-primary-200',
  'bg-purple-300': 'bg-primary-300',
  'bg-purple-400': 'bg-primary-400',
  'bg-purple-500': 'bg-primary-500',
  'bg-purple-600': 'bg-primary-600',
  'bg-purple-700': 'bg-primary-700',
  'bg-purple-800': 'bg-primary-800',
  'bg-purple-900': 'bg-primary-900',
  
  'text-violet-50': 'text-primary-50',
  'text-violet-100': 'text-primary-100',
  'text-violet-200': 'text-primary-200',
  'text-violet-300': 'text-primary-300',
  'text-violet-400': 'text-primary-400',
  'text-violet-500': 'text-primary-500',
  'text-violet-600': 'text-primary-600',
  'text-violet-700': 'text-primary-700',
  'text-violet-800': 'text-primary-800',
  'text-violet-900': 'text-primary-900',
  
  'text-purple-50': 'text-primary-50',
  'text-purple-100': 'text-primary-100',
  'text-purple-200': 'text-primary-200',
  'text-purple-300': 'text-primary-300',
  'text-purple-400': 'text-primary-400',
  'text-purple-500': 'text-primary-500',
  'text-purple-600': 'text-primary-600',
  'text-purple-700': 'text-primary-700',
  'text-purple-800': 'text-primary-800',
  'text-purple-900': 'text-primary-900',
  
  'border-violet-50': 'border-primary-50',
  'border-violet-100': 'border-primary-100',
  'border-violet-200': 'border-primary-200',
  'border-violet-300': 'border-primary-300',
  'border-violet-400': 'border-primary-400',
  'border-violet-500': 'border-primary-500',
  'border-violet-600': 'border-primary-600',
  'border-violet-700': 'border-primary-700',
  'border-violet-800': 'border-primary-800',
  'border-violet-900': 'border-primary-900',
  
  'border-purple-50': 'border-primary-50',
  'border-purple-100': 'border-primary-100',
  'border-purple-200': 'border-primary-200',
  'border-purple-300': 'border-primary-300',
  'border-purple-400': 'border-primary-400',
  'border-purple-500': 'border-primary-500',
  'border-purple-600': 'border-primary-600',
  'border-purple-700': 'border-primary-700',
  'border-purple-800': 'border-primary-800',
  'border-purple-900': 'border-primary-900',
  
  'ring-violet-50': 'ring-primary-50',
  'ring-violet-100': 'ring-primary-100',
  'ring-violet-200': 'ring-primary-200',
  'ring-violet-300': 'ring-primary-300',
  'ring-violet-400': 'ring-primary-400',
  'ring-violet-500': 'ring-primary-500',
  'ring-violet-600': 'ring-primary-600',
  'ring-violet-700': 'ring-primary-700',
  'ring-violet-800': 'ring-primary-800',
  'ring-violet-900': 'ring-primary-900',
  
  'ring-purple-50': 'ring-primary-50',
  'ring-purple-100': 'ring-primary-100',
  'ring-purple-200': 'ring-primary-200',
  'ring-purple-300': 'ring-primary-300',
  'ring-purple-400': 'ring-primary-400',
  'ring-purple-500': 'ring-primary-500',
  'ring-purple-600': 'ring-primary-600',
  'ring-purple-700': 'ring-primary-700',
  'ring-purple-800': 'ring-primary-800',
  'ring-purple-900': 'ring-primary-900',
  
  // Autres couleurs → Gris
  'bg-orange-': 'bg-gray-',
  'bg-pink-': 'bg-gray-',
  'bg-rose-': 'bg-gray-',
  'bg-amber-': 'bg-gray-',
  'bg-lime-': 'bg-gray-',
  'bg-emerald-': 'bg-gray-',
  'bg-teal-': 'bg-gray-',
  'bg-cyan-': 'bg-gray-',
  'bg-sky-': 'bg-gray-',
  'bg-slate-': 'bg-gray-',
  
  'text-orange-': 'text-gray-',
  'text-pink-': 'text-gray-',
  'text-rose-': 'text-gray-',
  'text-amber-': 'text-gray-',
  'text-lime-': 'text-gray-',
  'text-emerald-': 'text-gray-',
  'text-teal-': 'text-gray-',
  'text-cyan-': 'text-gray-',
  'text-sky-': 'text-gray-',
  'text-slate-': 'text-gray-',
  
  'border-orange-': 'border-gray-',
  'border-pink-': 'border-gray-',
  'border-rose-': 'border-gray-',
  'border-amber-': 'border-gray-',
  'border-lime-': 'border-gray-',
  'border-emerald-': 'border-gray-',
  'border-teal-': 'border-gray-',
  'border-cyan-': 'border-gray-',
  'border-sky-': 'border-gray-',
  'border-slate-': 'border-gray-',
  
  'ring-orange-': 'ring-gray-',
  'ring-pink-': 'ring-gray-',
  'ring-rose-': 'ring-gray-',
  'ring-amber-': 'ring-gray-',
  'ring-lime-': 'ring-gray-',
  'ring-emerald-': 'ring-gray-',
  'ring-teal-': 'ring-gray-',
  'ring-cyan-': 'ring-gray-',
  'ring-sky-': 'ring-gray-',
  'ring-slate-': 'ring-gray-',
  
  // Gradients
  'from-blue-': 'from-primary-',
  'to-blue-': 'to-primary-',
  'via-blue-': 'via-primary-',
  'from-indigo-': 'from-primary-',
  'to-indigo-': 'to-primary-',
  'via-indigo-': 'via-primary-',
  'from-violet-': 'from-primary-',
  'to-violet-': 'to-primary-',
  'via-violet-': 'via-primary-',
  'from-purple-': 'from-primary-',
  'to-purple-': 'to-primary-',
  'via-purple-': 'via-primary-',
  
  'from-red-': 'from-gray-',
  'to-red-': 'to-gray-',
  'via-red-': 'via-gray-',
  'from-yellow-': 'from-gray-',
  'to-yellow-': 'to-gray-',
  'via-yellow-': 'via-gray-',
  'from-orange-': 'from-gray-',
  'to-orange-': 'to-gray-',
  'via-orange-': 'via-gray-',
  'from-pink-': 'from-gray-',
  'to-pink-': 'to-gray-',
  'via-pink-': 'via-gray-',
  
  // Couleurs hexadécimales spécifiques
  '#ef4444': '#525252', // red-500 → gray-600
  '#dc2626': '#404040', // red-600 → gray-700
  '#b91c1c': '#262626', // red-700 → gray-800
  '#991b1b': '#171717', // red-800 → gray-900
  
  '#f59e0b': '#737373', // yellow-500 → gray-500
  '#d97706': '#525252', // yellow-600 → gray-600
  '#b45309': '#404040', // yellow-700 → gray-700
  '#92400e': '#262626', // yellow-800 → gray-800
  
  '#3b82f6': '#3da066', // blue-500 → primary-500
  '#2563eb': '#2f7f52', // blue-600 → primary-600
  '#1d4ed8': '#276543', // blue-700 → primary-700
  '#1e40af': '#225138', // blue-800 → primary-800
  
  '#6366f1': '#3da066', // indigo-500 → primary-500
  '#4f46e5': '#2f7f52', // indigo-600 → primary-600
  '#4338ca': '#276543', // indigo-700 → primary-700
  '#3730a3': '#225138', // indigo-800 → primary-800
  
  '#8b5cf6': '#3da066', // violet-500 → primary-500
  '#7c3aed': '#2f7f52', // violet-600 → primary-600
  '#6d28d9': '#276543', // violet-700 → primary-700
  '#5b21b6': '#225138', // violet-800 → primary-800
  
  '#a855f7': '#3da066', // purple-500 → primary-500
  '#9333ea': '#2f7f52', // purple-600 → primary-600
  '#7c3aed': '#276543', // purple-700 → primary-700
  '#6b21a8': '#225138', // purple-800 → primary-800
};

// Extensions de fichiers à traiter
const fileExtensions = ['.vue', '.js', '.jsx', '.ts', '.tsx', '.css', '.scss'];

// Fonction pour remplacer les couleurs dans un fichier
function replaceColorsInFile(filePath) {
  try {
    let content = fs.readFileSync(filePath, 'utf8');
    let hasChanges = false;
    
    // Appliquer tous les remplacements
    Object.entries(colorMappings).forEach(([oldColor, newColor]) => {
      if (content.includes(oldColor)) {
        content = content.replace(new RegExp(oldColor, 'g'), newColor);
        hasChanges = true;
      }
    });
    
    // Écrire le fichier si des changements ont été faits
    if (hasChanges) {
      fs.writeFileSync(filePath, content, 'utf8');
      return true;
    }
    
    return false;
  } catch (error) {
    console.error(`Erreur lors du traitement de ${filePath}:`, error.message);
    return false;
  }
}

// Fonction pour scanner un répertoire
function scanDirectory(dirPath, results = []) {
  const items = fs.readdirSync(dirPath);
  
  items.forEach(item => {
    const fullPath = path.join(dirPath, item);
    const stat = fs.statSync(fullPath);
    
    if (stat.isDirectory()) {
      // Ignorer les dossiers système
      if (!['node_modules', '.git', 'vendor', 'storage', 'bootstrap'].includes(item)) {
        scanDirectory(fullPath, results);
      }
    } else if (stat.isFile()) {
      const ext = path.extname(item);
      if (fileExtensions.includes(ext)) {
        const wasChanged = replaceColorsInFile(fullPath);
        if (wasChanged) {
          results.push(fullPath);
        }
      }
    }
  });
  
  return results;
}

// Fonction principale
function main() {
  console.log('🎨 Remplacement de toutes les couleurs non monochromes...\n');
  
  const startTime = Date.now();
  const changedFiles = scanDirectory('./resources/js');
  const endTime = Date.now();
  
  console.log(`⏱️  Traitement terminé en ${endTime - startTime}ms\n`);
  
  if (changedFiles.length === 0) {
    console.log('✅ Aucun fichier modifié - toutes les couleurs sont déjà monochromes !');
  } else {
    console.log(`🔄 ${changedFiles.length} fichier(s) modifié(s) :\n`);
    changedFiles.forEach(file => {
      console.log(`   📁 ${file}`);
    });
    
    console.log('\n🎯 Toutes les couleurs ont été remplacées par des couleurs monochromes :');
    console.log('   - Rouge → Gris');
    console.log('   - Jaune → Gris');
    console.log('   - Bleu → Vert (primary)');
    console.log('   - Indigo → Vert (primary)');
    console.log('   - Violet/Purple → Vert (primary)');
    console.log('   - Autres → Gris');
    
    console.log('\n✅ L\'application est maintenant totalement monochrome !');
  }
}

// Exécuter le script
if (require.main === module) {
  main();
}

module.exports = { colorMappings, replaceColorsInFile, scanDirectory };
