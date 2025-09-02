<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Thème Monochrome Total</h1>
        <p class="text-gray-600">Application entièrement en tons de vert et de gris - Style RIKEAA</p>
      </div>

      <!-- Palette de couleurs monochrome -->
      <div class="mb-12">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Palette Monochrome</h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div v-for="(color, key) in colorPalette" :key="key" class="text-center">
            <div 
              class="w-full h-20 rounded-lg mb-2 shadow-soft"
              :style="{ backgroundColor: color }"
            ></div>
            <div class="text-xs font-medium text-gray-700">{{ key }}</div>
            <div class="text-xs text-gray-500 font-mono">{{ color }}</div>
          </div>
        </div>
      </div>

      <!-- Composants UI monochrome -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Boutons -->
        <div class="rikeaa-card p-6">
          <h3 class="rikeaa-text-heading text-lg mb-4">Boutons Monochrome</h3>
          <div class="space-y-3">
            <button class="rikeaa-btn-primary w-full">Bouton Principal</button>
            <button class="rikeaa-btn-secondary w-full">Bouton Secondaire</button>
            <button class="rikeaa-btn-outline w-full">Bouton Contour</button>
          </div>
        </div>

        <!-- Cartes -->
        <div class="rikeaa-card p-6">
          <h3 class="rikeaa-text-heading text-lg mb-4">Cartes Monochrome</h3>
          <div class="space-y-3">
            <div class="rikeaa-card p-4">
              <h4 class="rikeaa-text-heading mb-2">Carte Standard</h4>
              <p class="rikeaa-text-body">Contenu avec ombre douce</p>
            </div>
            <div class="rikeaa-card-elevated p-4">
              <h4 class="rikeaa-text-heading mb-2">Carte Élevée</h4>
              <p class="rikeaa-text-body">Contenu avec ombre prononcée</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaires monochrome -->
      <div class="rikeaa-card p-6 mb-12">
        <h3 class="rikeaa-text-heading text-lg mb-4">Formulaires Monochrome</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Champ Standard</label>
            <input type="text" placeholder="Saisissez du texte..." class="rikeaa-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Champ avec Erreur</label>
            <input type="text" placeholder="Champ en erreur" class="rikeaa-input-error" />
          </div>
        </div>
      </div>

      <!-- Badges et étiquettes monochrome -->
      <div class="rikeaa-card p-6 mb-12">
        <h3 class="rikeaa-text-heading text-lg mb-4">Badges Monochrome</h3>
        <div class="flex flex-wrap gap-3">
          <span class="rikeaa-badge-primary">Primaire</span>
          <span class="rikeaa-badge-neutral">Neutre</span>
          <span class="rikeaa-badge-success">Succès</span>
          <span class="rikeaa-badge-warning">Attention</span>
          <span class="rikeaa-badge-error">Erreur</span>
        </div>
        <p class="text-sm text-gray-500 mt-3">Tous les badges utilisent uniquement des tons de vert et de gris</p>
      </div>

      <!-- Typographie monochrome -->
      <div class="rikeaa-card p-6 mb-12">
        <h3 class="rikeaa-text-heading text-lg mb-4">Typographie Monochrome</h3>
        <div class="space-y-3">
          <h1 class="rikeaa-text-heading text-2xl">Titre Principal</h1>
          <h2 class="rikeaa-text-subheading text-xl">Sous-titre</h2>
          <p class="rikeaa-text-body">Texte de corps avec une couleur neutre et lisible</p>
          <p class="rikeaa-text-caption">Légende ou texte secondaire</p>
          <a href="#" class="rikeaa-text-link">Lien avec couleur primaire</a>
        </div>
      </div>

      <!-- Grille de produits monochrome -->
      <div class="rikeaa-card p-6 mb-12">
        <h3 class="rikeaa-text-heading text-lg mb-4">Grille de Produits Monochrome</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="i in 6" :key="i" class="rikeaa-card overflow-hidden">
            <div class="h-32 bg-gray-200 rounded-t-lg"></div>
            <div class="p-3">
              <h4 class="rikeaa-text-heading text-sm mb-1">Produit {{ i }}</h4>
              <p class="rikeaa-text-body text-xs mb-2">Description courte du produit</p>
              <div class="flex justify-between items-center">
                <span class="text-primary-600 font-semibold">25.00 €</span>
                <button class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                  Voir
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation monochrome -->
      <div class="rikeaa-card p-6 mb-12">
        <h3 class="rikeaa-text-heading text-lg mb-4">Navigation Monochrome</h3>
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
          <div class="flex items-center space-x-4">
            <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">R</span>
            </div>
            <span class="text-lg font-bold text-primary-600">RIKEAA</span>
          </div>
          <div class="flex items-center space-x-4">
            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">Accueil</a>
            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">Produits</a>
            <a href="#" class="text-gray-600 hover:text-primary-600 transition-colors">Profil</a>
            <button class="rikeaa-btn-primary">Vendre</button>
          </div>
        </div>
      </div>

      <!-- Statuts et notifications monochrome -->
      <div class="rikeaa-card p-6">
        <h3 class="rikeaa-text-heading text-lg mb-4">Statuts et Notifications</h3>
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <span class="rikeaa-badge-primary">En vente</span>
            <span class="rikeaa-badge-neutral">Réservé</span>
            <span class="rikeaa-badge-neutral">Vendu</span>
          </div>
          <div class="flex items-center space-x-3">
            <span class="rikeaa-badge-success">Nouveau</span>
            <span class="rikeaa-badge-warning">Attention</span>
            <span class="rikeaa-badge-error">Erreur</span>
          </div>
          <p class="text-sm text-gray-500">Tous les statuts utilisent la palette monochrome verte et grise</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MonochromeThemeDemo',
  data() {
    return {
      colorPalette: {
        'primary-50': '#f0f9f4',
        'primary-100': '#dcf2e3',
        'primary-200': '#b8e4c8',
        'primary-300': '#8dd3a8',
        'primary-400': '#5bbd85',
        'primary-500': '#3da066',
        'primary-600': '#2f7f52',
        'primary-700': '#276543',
        'primary-800': '#225138',
        'primary-900': '#1e4330',
      }
    };
  }
};
</script>

<style scoped>
/* Styles spécifiques au composant de démonstration monochrome */
.rikeaa-card:hover {
  transform: translateY(-2px);
}

.rikeaa-card-elevated:hover {
  transform: translateY(-4px);
}

/* Toutes les couleurs sont maintenant monochromes */
:deep(.text-primary-600) {
  color: #3da066;
}

:deep(.text-primary-700) {
  color: #276543;
}

:deep(.bg-primary-500) {
  background-color: #3da066;
}

:deep(.bg-primary-100) {
  background-color: #dcf2e3;
}
</style>
