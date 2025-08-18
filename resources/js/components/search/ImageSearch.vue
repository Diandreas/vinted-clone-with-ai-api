<template>
  <div class="image-search-container px-2 sm:px-4">
    <!-- Header - Ultra Compact -->
    <div class="text-center mb-3 sm:mb-6">
      <h1 class="text-lg sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-3">Recherche par Image</h1>
      <p class="text-gray-600 max-w-2xl mx-auto text-xs sm:text-sm">
        Trouvez des produits similaires en uploadant une photo. Notre IA analyse l'image et vous propose les articles les plus ressemblants.
      </p>
    </div>

    <!-- Upload Zone - Ultra Compact -->
    <div class="upload-section mb-3 sm:mb-6">
      <div 
        class="upload-zone border-2 border-dashed border-gray-300 rounded-md p-3 sm:p-6 text-center transition-colors hover:border-primary-400"
        :class="{ 
          'border-primary-500 bg-primary-50': isDragOver,
          'border-green-500 bg-green-50': uploadedImage
        }"
        @dragover.prevent="handleDragOver"
        @dragleave.prevent="handleDragLeave"
        @drop.prevent="handleDrop"
      >
        <!-- Image Preview - Ultra Compact -->
        <div v-if="uploadedImage" class="mb-2 sm:mb-3">
          <img 
            :src="imagePreview" 
            alt="Image uploadée"
            class="max-h-32 sm:max-h-48 mx-auto rounded-md shadow-sm"
          >
          <button 
            @click="clearImage"
            class="mt-2 text-xs sm:text-sm text-gray-700 hover:text-gray-900"
          >
            <i class="fas fa-trash mr-1"></i>
            Supprimer l'image
          </button>
        </div>

        <!-- Upload Interface - Ultra Compact -->
        <div v-else>
          <div class="mb-2 sm:mb-3">
            <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl text-gray-400 mb-1 sm:mb-2"></i>
          </div>
          <p class="text-sm sm:text-base font-medium text-gray-700 mb-1 sm:mb-2">
            Glissez votre image ici ou cliquez pour parcourir
          </p>
          <p class="text-xs sm:text-sm text-gray-500 mb-2 sm:mb-3">
            Formats supportés: JPEG, PNG, GIF, SVG (max 10MB)
          </p>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleFileSelect"
            class="hidden"
          >
          <button
            @click="$refs.fileInput.click()"
            class="bg-primary-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-md hover:bg-primary-700 transition-colors text-xs sm:text-sm"
          >
            <i class="fas fa-folder-open mr-1"></i>
            Choisir une image
          </button>
        </div>
      </div>

      <!-- Search Button - Ultra Compact -->
      <div v-if="uploadedImage" class="text-center mt-2 sm:mt-3">
        <button
          @click="searchByImage"
          :disabled="isSearching"
          class="bg-green-600 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-md font-medium hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
        >
          <i v-if="isSearching" class="fas fa-spinner fa-spin mr-1"></i>
          <i v-else class="fas fa-search mr-1"></i>
          {{ isSearching ? 'Recherche en cours...' : 'Rechercher des produits similaires' }}
        </button>

        <!-- Options - Ultra Compact -->
        <div class="mt-2 sm:mt-3 flex justify-center items-center space-x-2 sm:space-x-3">
          <label class="flex items-center text-xs sm:text-sm text-gray-600">
            <span class="mr-1 sm:mr-2">Résultats:</span>
            <select v-model="searchLimit" class="border border-gray-300 rounded px-1 py-0.5 sm:px-2 sm:py-1 text-xs sm:text-sm">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="30">30</option>
            </select>
          </label>
        </div>
      </div>
    </div>

    <!-- Loading State - Ultra Compact -->
    <div v-if="isSearching" class="text-center py-4 sm:py-6">
      <div class="animate-spin rounded-full h-8 w-8 sm:h-10 sm:w-10 border-b-2 border-primary-600 mx-auto mb-2 sm:mb-3"></div>
      <p class="text-gray-600 text-xs sm:text-sm">Analyse de l'image en cours...</p>
    </div>

    <!-- Error State - Ultra Compact -->
    <div v-if="error" class="bg-gray-50 border border-gray-200 rounded-md p-2 sm:p-3 mb-3 sm:mb-4">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-gray-500 mr-1 sm:mr-2 text-sm"></i>
        <span class="text-gray-800 text-xs sm:text-sm">{{ error }}</span>
      </div>
    </div>

    <!-- Results -->
    <SearchResults 
      v-if="hasSearched && !isSearching && searchResults.length > 0"
      :results="searchResults"
      :search-meta="searchMeta"
      @product-click="handleProductClick"
    />

    <!-- Empty State - Ultra Compact -->
    <div v-else-if="hasSearched && !isSearching && searchResults.length === 0" class="text-center py-6 sm:py-8">
      <i class="fas fa-search text-3xl sm:text-4xl text-gray-300 mb-2 sm:mb-3"></i>
      <h3 class="text-sm sm:text-lg font-medium text-gray-700 mb-1 sm:mb-2">Aucun produit similaire trouvé</h3>
      <p class="text-gray-500 text-xs sm:text-sm">Essayez avec une autre image ou ajustez vos critères de recherche.</p>
    </div>

    <!-- Tips Section -->
    <div v-if="!uploadedImage" class="tips-section mt-12">
      <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center">
        Conseils pour une recherche optimale
      </h3>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="tip-card text-center p-6 bg-gray-50 rounded-lg">
          <i class="fas fa-camera text-3xl text-primary-500 mb-3"></i>
          <h4 class="font-medium text-gray-800 mb-2">Image claire</h4>
          <p class="text-sm text-gray-600">
            Utilisez des photos nettes avec un bon éclairage pour de meilleurs résultats.
          </p>
        </div>
        <div class="tip-card text-center p-6 bg-gray-50 rounded-lg">
          <i class="fas fa-crop-alt text-3xl text-green-500 mb-3"></i>
          <h4 class="font-medium text-gray-800 mb-2">Cadrage serré</h4>
          <p class="text-sm text-gray-600">
            Cadrrez l'objet principal pour une analyse plus précise.
          </p>
        </div>
        <div class="tip-card text-center p-6 bg-gray-50 rounded-lg">
          <i class="fas fa-palette text-3xl text-primary-500 mb-3"></i>
          <h4 class="font-medium text-gray-800 mb-2">Couleurs fidèles</h4>
          <p class="text-sm text-gray-600">
            Évitez les filtres pour que les couleurs soient authentiques.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SearchResults from './SearchResults.vue';
import { useNotificationStore } from '../../stores/notification';

export default {
  name: 'ImageSearch',
  components: {
    SearchResults
  },
  setup() {
    const notificationStore = useNotificationStore();
    return {
      notificationStore
    };
  },
  data() {
    return {
      uploadedImage: null,
      imagePreview: null,
      isDragOver: false,
      isSearching: false,
      searchResults: [],
      searchMeta: null,
      error: null,
      hasSearched: false,
      searchLimit: 10
    };
  },
  methods: {
    handleDragOver(e) {
      this.isDragOver = true;
    },
    
    handleDragLeave(e) {
      this.isDragOver = false;
    },
    
    handleDrop(e) {
      this.isDragOver = false;
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        this.processFile(files[0]);
      }
    },
    
    handleFileSelect(e) {
      const file = e.target.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    
    processFile(file) {
      // Validation
      if (!file.type.startsWith('image/')) {
        this.showError('Veuillez sélectionner un fichier image valide.');
        return;
      }
      
      if (file.size > 10 * 1024 * 1024) { // 10MB
        this.showError('La taille de l\'image ne doit pas dépasser 10MB.');
        return;
      }
      
      this.uploadedImage = file;
      
      // Créer l'aperçu
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagePreview = e.target.result;
      };
      reader.readAsDataURL(file);
      
      this.clearError();
      this.clearResults();
    },
    
    clearImage() {
      this.uploadedImage = null;
      this.imagePreview = null;
      this.clearResults();
      this.$refs.fileInput.value = '';
    },
    
    clearResults() {
      this.searchResults = [];
      this.searchMeta = null;
      this.hasSearched = false;
    },
    
    clearError() {
      this.error = null;
    },
    
    showError(message) {
      this.error = message;
      this.notificationStore.error(message);
    },
    
    async searchByImage() {
      if (!this.uploadedImage) {
        this.showError('Veuillez d\'abord sélectionner une image.');
        return;
      }
      
      this.isSearching = true;
      this.clearError();
      
      try {
        const formData = new FormData();
        formData.append('image', this.uploadedImage);
        formData.append('limit', this.searchLimit);
        
        const response = await fetch('/api/v1/search/image', {
          method: 'POST',
          body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.searchResults = data.data.results || [];
          this.searchMeta = data.data.search_meta;
          this.hasSearched = true;
          
          if (this.searchResults.length > 0) {
            this.notificationStore.success(`${this.searchResults.length} produit(s) similaire(s) trouvé(s)`);
            
            // Scroll vers les résultats
            this.$nextTick(() => {
              const resultsElement = document.querySelector('.search-results');
              if (resultsElement) {
                resultsElement.scrollIntoView({ 
                  behavior: 'smooth',
                  block: 'start'
                });
              }
            });
          } else {
            console.warn('Search returned 0 results despite success:', data);
          }
        } else {
          throw new Error(data.message || 'Erreur lors de la recherche');
        }
      } catch (error) {
        console.error('Search error:', error);
        this.showError(
          error.message || 
          'Une erreur est survenue lors de la recherche. Veuillez réessayer.'
        );
      } finally {
        this.isSearching = false;
      }
    },
    
    handleProductClick(product) {
      // Navigation vers la page du produit
      this.$router.push(`/products/${product.id}`);
    }
  }
};
</script>

<style scoped>
.image-search-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.upload-zone {
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-zone:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tip-card {
  transition: transform 0.2s ease;
}

.tip-card:hover {
  transform: translateY(-4px);
}

@media (max-width: 768px) {
  .image-search-container {
    padding: 1rem;
  }
  
  .upload-zone {
    padding: 2rem 1rem;
  }
}
</style>