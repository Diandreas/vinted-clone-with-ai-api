<template>
  <div class="search-results px-2 sm:px-4">
    <!-- Header - Ultra Compact -->
             <!-- Match Details - Ultra Compact -->
          <div class="match-details mb-1 sm:mb-2">
            <button
              @click.stop="toggleMatchDetails(result.product.id)"
              class="text-xs text-primary-600 hover:text-primary-800 flex items-center"
            >
              <i class="fas fa-info-circle mr-0.5"></i>
              <span class="hidden sm:inline">Détails correspondance</span>
              <span class="sm:hidden">Détails</span>
              <i 
                class="fas fa-chevron-down ml-1 transition-transform"
                :class="{ 'rotate-180': expandedDetails.includes(result.product.id) }"
              ></i>
            </button>results-header mb-3 sm:mb-4">
      <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-1 sm:mb-2">
        Résultats de la recherche
      </h2>
      <div class="flex flex-wrap items-center justify-between text-xs sm:text-sm text-gray-600">
        <span>{{ results.length }} produit(s) trouvé(s)</span>
        <div class="flex items-center space-x-2 sm:space-x-3">
          <span v-if="searchMeta" class="hidden sm:inline">
            Algorithme v{{ searchMeta.algorithm_version }}
          </span>
          <button
            @click="sortBy = sortBy === 'similarity' ? 'price' : 'similarity'"
            class="flex items-center text-primary-600 hover:text-primary-800 text-xs sm:text-sm"
          >
            <i class="fas fa-sort mr-1"></i>
            Trier par {{ sortBy === 'similarity' ? 'prix' : 'pertinence' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Results Grid - Ultra Compact -->
    <div class="results-grid grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-4">
      <div
        v-for="(result, index) in sortedResults"
        :key="result.product.id"
        class="result-card bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
        @click="$emit('product-click', result.product)"
      >
        <!-- Image - Ultra Compact -->
        <div class="relative">
          <!-- Image réelle du produit -->
          <img
            v-if="getImageUrl(result.product)"
            :src="getImageUrl(result.product)"
            :alt="result.product.title"
            class="w-full h-24 sm:h-32 object-cover"
            loading="lazy"
            @error="handleImageError($event, result.product)"
          >
          
          <!-- Placeholder uniquement si aucune image n'est disponible -->
          <div
            v-else
            class="w-full h-24 sm:h-32 bg-gray-100 flex items-center justify-center"
          >
            <div class="text-center text-gray-400">
              <i class="fas fa-image text-xl mb-1"></i>
              <p class="text-xs">Pas d'image</p>
            </div>
          </div>

          <!-- Similarity Badge - Ultra Compact -->
          <div class="absolute top-1 right-1">
            <div
              class="similarity-badge px-1 py-0.5 rounded-full text-xs font-medium text-white"
              :class="getSimilarityBadgeClass(result.similarity_score)"
            >
              {{ result.similarity_score }}%
            </div>
          </div>

          <!-- Boost Badge - Ultra Compact -->
          <div v-if="result.product.is_boosted" class="absolute top-1 left-1">
            <div class="bg-gray-500 text-white px-1 py-0.5 rounded-full text-xs font-medium">
              <i class="fas fa-rocket mr-0.5"></i>
              <span class="hidden sm:inline">Boosté</span>
            </div>
          </div>
        </div>

        <!-- Content - Ultra Compact -->
        <div class="p-2 sm:p-3">
          <!-- Title and Price - Ultra Compact -->
          <div class="mb-1 sm:mb-2">
            <h3 class="font-medium text-gray-900 mb-0.5 line-clamp-2 text-xs sm:text-sm">
              {{ result.product.title }}
            </h3>
            <div class="flex items-center justify-between">
              <span class="text-sm sm:text-lg font-bold text-green-600">
                {{ result.product.formatted_price }}
              </span>
              <span v-if="result.product.original_price" class="text-xs text-gray-500 line-through">
                {{ result.product.formatted_original_price }}
              </span>
            </div>
          </div>

          <!-- Product Info - Ultra Compact -->
          <div class="mb-1 sm:mb-2 space-y-0.5 sm:space-y-1">
            <div v-if="result.product.brand" class="flex items-center text-xs text-gray-600">
              <i class="fas fa-tag mr-1 text-gray-400 text-xs"></i>
              {{ result.product.brand.name }}
            </div>
            <div v-if="result.product.size" class="flex items-center text-xs text-gray-600">
              <i class="fas fa-ruler mr-1 text-gray-400 text-xs"></i>
              Taille {{ result.product.size }}
            </div>
            <div v-if="result.product.condition" class="flex items-center text-xs text-gray-600">
              <i class="fas fa-check-circle mr-1 text-gray-400 text-xs"></i>
              {{ result.product.condition.name }}
            </div>
          </div>

          <!-- Match Details -->
          <div class="match-details mb-3">
            <button
              @click.stop="toggleMatchDetails(result.product.id)"
              class="text-xs text-primary-600 hover:text-primary-800 flex items-center"
            >
              <i class="fas fa-info-circle mr-1"></i>
              Détails de la correspondance
              <i
                class="fas fa-chevron-down ml-1 transition-transform"
                :class="{ 'rotate-180': expandedDetails.includes(result.product.id) }"
              ></i>
            </button>

            <div
              v-if="expandedDetails.includes(result.product.id)"
              class="mt-2 p-2 bg-gray-50 rounded text-xs"
            >
              <!-- Labels similaires -->
              <div v-if="result.match_details.labels && result.match_details.labels.length > 0" class="mb-2">
                <strong class="text-gray-700">Labels détectés:</strong>
                <div class="flex flex-wrap gap-1 mt-1">
                  <span
                    v-for="label in result.match_details.labels.slice(0, 3)"
                    :key="label.description"
                    class="bg-primary-100 text-primary-800 px-2 py-1 rounded"
                  >
                    {{ label.description }} ({{ Math.round(label.score * 100) }}%)
                  </span>
                </div>
              </div>

              <!-- Objets détectés -->
              <div v-if="result.match_details.objects && result.match_details.objects.length > 0" class="mb-2">
                <strong class="text-gray-700">Objets détectés:</strong>
                <div class="flex flex-wrap gap-1 mt-1">
                  <span
                    v-for="object in result.match_details.objects.slice(0, 3)"
                    :key="object.name"
                    class="bg-green-100 text-green-800 px-2 py-1 rounded"
                  >
                    {{ object.name }} ({{ Math.round(object.score * 100) }}%)
                  </span>
                </div>
              </div>

              <!-- Couleurs dominantes -->
              <div v-if="result.match_details.dominant_colors && result.match_details.dominant_colors.length > 0">
                <strong class="text-gray-700">Couleurs dominantes:</strong>
                <div class="flex gap-1 mt-1">
                  <div
                    v-for="color in result.match_details.dominant_colors.slice(0, 5)"
                    :key="color.red + color.green + color.blue"
                    class="w-4 h-4 rounded border border-gray-300"
                    :style="{ backgroundColor: `rgb(${color.red}, ${color.green}, ${color.blue})` }"
                    :title="`RGB(${color.red}, ${color.green}, ${color.blue}) - ${Math.round(color.pixel_fraction * 100)}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- User and Actions - Ultra Compact -->
          <div class="flex items-center justify-between">
            <div class="flex items-center text-xs text-gray-600">
              <img
                :src="getUserAvatarUrl(result.product.user)"
                :alt="result.product.user?.name"
                class="w-3 h-3 sm:w-4 sm:h-4 rounded-full mr-1"
                @error="handleAvatarError"
              >
              <span class="truncate">{{ result.product.user?.name }}</span>
            </div>

            <div class="flex items-center space-x-1 sm:space-x-2">
              <button
                @click.stop="toggleFavorite(result.product)"
                class="text-gray-400 hover:text-gray-500 transition-colors text-xs"
                :class="{ 'text-gray-500': result.product.is_favorited_by_user }"
              >
                <i class="fas fa-heart"></i>
              </button>
              <button
                @click.stop="toggleLike(result.product)"
                class="text-gray-400 hover:text-primary-500 transition-colors flex items-center text-xs"
                :class="{ 'text-primary-500': result.product.is_liked_by_user }"
              >
                <i class="fas fa-thumbs-up mr-0.5"></i>
                <span class="text-xs">{{ result.product.likes_count }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button - Ultra Compact -->
    <div v-if="canLoadMore" class="text-center mt-4 sm:mt-6">
      <button
        @click="$emit('load-more')"
        class="bg-primary-600 text-white px-4 py-2 sm:px-6 sm:py-2 rounded-md hover:bg-primary-700 transition-colors text-xs sm:text-sm"
      >
        Voir plus de résultats
      </button>
    </div>
  </div>
</template>

<script>
import { useNotificationStore } from '../../stores/notification';

export default {
  name: 'SearchResults',
  props: {
    results: {
      type: Array,
      required: true,
      default: () => []
    },
    searchMeta: {
      type: Object,
      default: () => ({})
    },
    canLoadMore: {
      type: Boolean,
      default: false
    }
  },
  emits: ['product-click', 'load-more'],
  setup() {
    const notificationStore = useNotificationStore();
    return {
      notificationStore
    };
  },
  data() {
    return {
      sortBy: 'similarity', // 'similarity' ou 'price'
      expandedDetails: []
    };
  },
  computed: {
    sortedResults() {
      const results = [...this.results];

      if (this.sortBy === 'similarity') {
        return results.sort((a, b) => b.similarity_score - a.similarity_score);
      } else if (this.sortBy === 'price') {
        return results.sort((a, b) => a.product.price - b.product.price);
      }

      return results;
    }
  },
  methods: {
    getSimilarityBadgeClass(score) {
      if (score >= 80) return 'bg-green-500';
      if (score >= 60) return 'bg-gray-500';
      if (score >= 40) return 'bg-gray-500';
      return 'bg-gray-500';
    },

    toggleMatchDetails(productId) {
      const index = this.expandedDetails.indexOf(productId);
      if (index > -1) {
        this.expandedDetails.splice(index, 1);
      } else {
        this.expandedDetails.push(productId);
      }
    },

    async toggleFavorite(product) {
      // Vérifier l'authentification
      const user = this.$store?.state?.auth?.user;
      if (!user) {
        this.notificationStore.warning('Connectez-vous pour ajouter aux favoris');
        return;
      }

      try {
        const response = await fetch(`/api/v1/products/${product.id}/favorite`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${user.token}`,
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          product.is_favorited_by_user = data.data.favorited;
        }
      } catch (error) {
        console.error('Favorite error:', error);
        this.notificationStore.error('Erreur lors de la mise à jour des favoris');
      }
    },

    async toggleLike(product) {
      const user = this.$store?.state?.auth?.user;
      if (!user) {
        this.notificationStore.warning('Connectez-vous pour liker');
        return;
      }

      try {
        const response = await fetch(`/api/v1/products/${product.id}/like`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${user.token}`,
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          product.is_liked_by_user = data.data.liked;
          product.likes_count += data.data.liked ? 1 : -1;
        }
      } catch (error) {
        console.error('Like error:', error);
        this.notificationStore.error('Erreur lors du like');
      }
    },

    getImageUrl(product) {
      // Priorité 1: Image principale
      if (product.main_image && product.main_image.trim() !== '') {
        return product.main_image;
      }
      
      // Priorité 2: Première image de la galerie
      if (product.images && product.images.length > 0) {
        const firstImage = product.images[0];
        if (firstImage && (firstImage.url || firstImage.image_url)) {
          const imageUrl = firstImage.url || firstImage.image_url;
          return imageUrl;
        }
      }
      
      // Priorité 3: Image URL générique
      if (product.image_url && product.image_url.trim() !== '') {
        return product.image_url;
      }
      
      // Priorité 4: Image uploadée (file object)
      if (product.uploaded_image) {
        return product.uploaded_image;
      }
      
      // Dernière option: Pas d'image disponible
      return null;
    },

    handleImageError(event, product) {
      console.warn('Image loading failed for product:', product.id, 'URL:', event.target.src);
      
      // Essayer les autres sources d'images disponibles
      const currentSrc = event.target.src;
      
      // Si on a échoué sur main_image_url, essayer images[0]
      if (product.images && product.images.length > 0 && currentSrc !== (product.images[0].url || product.images[0].image_url)) {
        const fallbackUrl = product.images[0].url || product.images[0].image_url;
        if (fallbackUrl && fallbackUrl.trim() !== '') {
          event.target.src = fallbackUrl;
          return;
        }
      }
      
      // Si on a échoué sur images[0], essayer image_url
      if (product.image_url && currentSrc !== product.image_url) {
        event.target.src = product.image_url;
        return;
      }
      
      // En dernier recours, masquer l'image et afficher le placeholder
      event.target.style.display = 'none';
      
      // Créer et afficher un placeholder personnalisé
      const placeholder = document.createElement('div');
      placeholder.className = 'w-full h-24 sm:h-32 bg-gray-100 flex items-center justify-center';
      placeholder.innerHTML = `
        <div class="text-center text-gray-400">
          <i class="fas fa-image text-xl mb-1"></i>
          <p class="text-xs">Image indisponible</p>
        </div>
      `;
      
      event.target.parentNode.appendChild(placeholder);
    },

    hasValidImage(product) {
      return (
        (product.main_image_url && product.main_image_url.trim() !== '') ||
        (product.images && product.images.length > 0 && (product.images[0].url || product.images[0].image_url)) ||
        (product.image_url && product.image_url.trim() !== '') ||
        product.uploaded_image
      );
    },

    getUserAvatarUrl(user) {
      if (!user) return '/default-avatar.png';
      
      if (user.avatar_url) {
        return user.avatar_url;
      }
      
      if (user.avatar) {
        return user.avatar;
      }
      
      return '/default-avatar.png';
    },

    handleAvatarError(event) {
      if (event.target.src !== '/default-avatar.png') {
        event.target.src = '/default-avatar.png';
      } else {
        // Avatar par défaut en SVG si le fichier n'existe pas
        event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiNGM0Y0RjYiLz4KPGNpcmNsZSBjeD0iMjAiIGN5PSIxNiIgcj0iNiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNOCAzMkM4IDI2LjQ3NzIgMTIuNDc3MiAyMiAxOCAyMkgyMkMyNy41MjI4IDIyIDMyIDI2LjQ3NzIgMzIgMzJWNDBIOFYzMloiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+Cg==';
      }
    }
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.similarity-badge {
  backdrop-filter: blur(4px);
}

.result-card {
  transition: all 0.2s ease;
}

.result-card:hover {
  transform: translateY(-2px);
}

.match-details .rotate-180 {
  transform: rotate(180deg);
}

@media (max-width: 640px) {
  .results-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }
  
  .result-card {
    font-size: 0.75rem;
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .results-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}
</style>
