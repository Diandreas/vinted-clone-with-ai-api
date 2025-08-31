<template>
  <div class="search-results px-2 sm:px-4">
    <!-- Header - Ultra Compact -->
    <div class="results-header mb-3 sm:mb-4">
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
    <div v-if="sortedResults.length > 0" class="results-grid grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-4">
      <div
        v-for="(result, index) in sortedResults"
        :key="result.product?.id || index"
        v-if="result && result.product"
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
              :class="getSimilarityBadgeClass(result.similarity_score || 0)"
            >
              {{ (result.similarity_score || 0).toFixed(0) }}%
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
              {{ result.product.title || 'Sans titre' }}
            </h3>
            <div class="flex items-center justify-between">
              <span class="text-sm sm:text-lg font-bold text-green-600">
                {{ result.product.formatted_price || 'Prix non disponible' }}
              </span>
              <span v-if="result.product.original_price" class="text-xs text-gray-500 line-through">
                {{ result.product.formatted_original_price }}
              </span>
            </div>
          </div>

          <!-- User Info - Ultra Compact -->
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1 sm:mb-2">
            <div class="flex items-center">
              <img
                :src="getUserAvatarUrl(result.product.user)"
                :alt="result.product.user?.name || 'Utilisateur'"
                class="w-4 h-4 rounded-full mr-1"
                @error="handleAvatarError"
              >
              <span class="truncate max-w-20">{{ result.product.user?.name || 'Utilisateur' }}</span>
            </div>
            <span class="text-xs">{{ result.product.created_at_formatted || 'Date non disponible' }}</span>
          </div>

          <!-- Actions - Ultra Compact -->
          <div class="flex items-center justify-between text-xs">
            <button
              @click.stop="toggleFavorite(result.product)"
              class="text-gray-400 hover:text-red-500 transition-colors flex items-center"
              :class="{ 'text-red-500': result.product.is_favorited_by_user }"
            >
              <i class="fas fa-heart"></i>
            </button>
            <button
              @click.stop="toggleLike(result.product)"
              class="text-gray-400 hover:text-primary-500 transition-colors flex items-center text-xs"
              :class="{ 'text-primary-500': result.product.is_liked_by_user }"
            >
              <i class="fas fa-thumbs-up mr-0.5"></i>
              <span class="text-xs">{{ result.product.likes_count || 0 }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- No Results Message -->
    <div v-else-if="results.length > 0" class="text-center py-8">
      <i class="fas fa-exclamation-triangle text-3xl text-yellow-500 mb-3"></i>
      <h3 class="text-lg font-medium text-gray-700 mb-2">Aucun résultat valide</h3>
      <p class="text-gray-500 text-sm">Les résultats de recherche contiennent des données invalides. Veuillez réessayer.</p>
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

// Fonctions de génération d'avatar dynamique
const generateDefaultAvatar = (name, id) => {
  const initials = getUserInitials(name)
  const color = generateUserColor(name || id?.toString() || 'User')
  
  const svg = `
    <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
      <rect width="40" height="40" fill="${color}"/>
      <text x="50%" y="50%" text-anchor="middle" dy="0.35em" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">
        ${initials}
      </text>
    </svg>
  `
  
  return 'data:image/svg+xml;base64,' + btoa(svg)
}

const getUserInitials = (name) => {
  if (!name) return '?'
  const cleanName = name.trim()
  const names = cleanName.split(' ')
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase()
  } else {
    return names[0].charAt(0).toUpperCase() + names[names.length - 1].charAt(0).toUpperCase()
  }
}

const generateUserColor = (name) => {
  if (!name) return '#6B7280'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#06B6D4']
  return colors[Math.abs(hash) % colors.length]
}

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
      // Filter out any results that don't have a valid product
      const validResults = this.results.filter(result => result && result.product);
      
      if (this.sortBy === 'similarity') {
        return validResults.sort((a, b) => b.similarity_score - a.similarity_score);
      } else if (this.sortBy === 'price') {
        return validResults.sort((a, b) => (a.product.price || 0) - (b.product.price || 0));
      }

      return validResults;
    }
  },
  watch: {
    results: {
      handler(newResults) {
        if (newResults && newResults.length > 0) {
          const invalidResults = newResults.filter(result => !result || !result.product);
          if (invalidResults.length > 0) {
            console.warn(`SearchResults: Received ${invalidResults.length} invalid results:`, invalidResults);
          }
          
          // Log the structure of valid results for debugging
          const validResults = newResults.filter(result => result && result.product);
          if (validResults.length > 0) {
            console.log('SearchResults: Valid result structure:', validResults[0]);
          }
        }
      },
      immediate: true
    }
  },
  methods: {
    getSimilarityBadgeClass(score) {
      if (!score || isNaN(score)) return 'bg-gray-500';
      if (score >= 80) return 'bg-green-500';
      if (score >= 60) return 'bg-gray-500';
      if (score >= 40) return 'bg-gray-500';
      return 'bg-gray-500';
    },

    toggleMatchDetails(productId) {
      if (!productId) return;
      
      const index = this.expandedDetails.indexOf(productId);
      if (index > -1) {
        this.expandedDetails.splice(index, 1);
      } else {
        this.expandedDetails.push(productId);
      }
    },

    async toggleFavorite(product) {
      if (!product || !product.id) return;
      
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
      if (!product || !product.id) return;
      
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
          product.likes_count = (product.likes_count || 0) + (data.data.liked ? 1 : -1);
        }
      } catch (error) {
        console.error('Like error:', error);
        this.notificationStore.error('Erreur lors du like');
      }
    },

    getImageUrl(product) {
      if (!product) return null;
      
      // Priorité 1: Image principale
      if (product.main_image && product.main_image.trim() !== '') {
        return product.main_image;
      }
      
      // Priorité 2: URL de l'image principale
      if (product.main_image_url && product.main_image_url.trim() !== '') {
        return product.main_image_url;
      }
      
      // Priorité 3: Première image du tableau d'images
      if (product.images && product.images.length > 0) {
        const firstImage = product.images[0];
        if (firstImage.url && firstImage.url.trim() !== '') {
          return firstImage.url;
        }
        if (firstImage.image_url && firstImage.image_url.trim() !== '') {
          return firstImage.image_url;
        }
      }
      
      // Priorité 4: URL d'image générique
      if (product.image_url && product.image_url.trim() !== '') {
        return product.image_url;
      }
      
      // Priorité 5: Image uploadée
      if (product.uploaded_image && product.uploaded_image.trim() !== '') {
        return product.uploaded_image;
      }
      
      return null;
    },

    handleImageError(event, product) {
      if (!product) return;
      
      const currentSrc = event.target.src;
      
      // Si on a échoué sur main_image, essayer main_image_url
      if (product.main_image_url && currentSrc !== product.main_image_url) {
        event.target.src = product.main_image_url;
        return;
      }
      
      // Si on a échoué sur main_image, essayer images[0]
      if (product.images && product.images.length > 0) {
        const fallbackUrl = product.images[0].url || product.images[0].image_url;
        if (fallbackUrl && fallbackUrl.trim() !== '' && currentSrc !== fallbackUrl) {
          event.target.src = fallbackUrl;
          return;
        }
      }
      
      // Si on a échoué, essayer image_url
      if (product.image_url && currentSrc !== product.image_url) {
        event.target.src = product.image_url;
        return;
      }
      
      // En dernier recours, afficher le placeholder par défaut
      event.target.src = '/images/placeholder-product.png';
    },

    hasValidImage(product) {
      if (!product) return false;
      
      return (
        (product.main_image && product.main_image.trim() !== '') ||
        (product.main_image_url && product.main_image_url.trim() !== '') ||
        (product.images && product.images.length > 0 && (product.images[0].url || product.images[0].image_url)) ||
        (product.image_url && product.image_url.trim() !== '') ||
        product.uploaded_image
      );
    },

    getUserAvatarUrl(user) {
      if (!user) return generateDefaultAvatar('User', null);
      
      if (user.avatar_url) {
        return user.avatar_url;
      }
      
      if (user.avatar) {
        return user.avatar;
      }
      
      return generateDefaultAvatar(user.name, user.id);
    },

    handleAvatarError(event) {
      // Essayer de récupérer les infos de l'utilisateur depuis les attributs de l'élément
      const userElement = event.target.closest('.flex').querySelector('span');
      const userName = userElement ? userElement.textContent : 'User';
      const dynamicAvatar = generateDefaultAvatar(userName, null);
      
      if (event.target.src !== dynamicAvatar) {
        event.target.src = dynamicAvatar;
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
