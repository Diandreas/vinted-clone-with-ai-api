<template>
  <div class="search-results">
    <!-- Header -->
    <div class="results-header mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">
        Résultats de la recherche
      </h2>
      <div class="flex flex-wrap items-center justify-between text-sm text-gray-600">
        <span>{{ results.length }} produit(s) trouvé(s)</span>
        <div class="flex items-center space-x-4">
          <span v-if="searchMeta">
            Algorithme v{{ searchMeta.algorithm_version }}
          </span>
          <button
            @click="sortBy = sortBy === 'similarity' ? 'price' : 'similarity'"
            class="flex items-center text-blue-600 hover:text-blue-800"
          >
            <i class="fas fa-sort mr-1"></i>
            Trier par {{ sortBy === 'similarity' ? 'prix' : 'pertinence' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Results Grid -->
    <div class="results-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="(result, index) in sortedResults"
        :key="result.product.id"
        class="result-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
        @click="$emit('product-click', result.product)"
      >
        <!-- Image -->
        <div class="relative">
          <img
            :src="result.product.main_image_url || '/placeholder-product.jpg'"
            :alt="result.product.title"
            class="w-full h-48 object-cover"
            loading="lazy"
          >

          <!-- Similarity Badge -->
          <div class="absolute top-2 right-2">
            <div
              class="similarity-badge px-2 py-1 rounded-full text-xs font-medium text-white"
              :class="getSimilarityBadgeClass(result.similarity_score)"
            >
              {{ result.similarity_score }}% de similarité
            </div>
          </div>

          <!-- Boost Badge -->
          <div v-if="result.product.is_boosted" class="absolute top-2 left-2">
            <div class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">
              <i class="fas fa-rocket mr-1"></i>
              Boosté
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <!-- Title and Price -->
          <div class="mb-3">
            <h3 class="font-medium text-gray-900 mb-1 line-clamp-2">
              {{ result.product.title }}
            </h3>
            <div class="flex items-center justify-between">
              <span class="text-xl font-bold text-green-600">
                {{ result.product.formatted_price }}
              </span>
              <span v-if="result.product.original_price" class="text-sm text-gray-500 line-through">
                {{ result.product.formatted_original_price }}
              </span>
            </div>
          </div>

          <!-- Product Info -->
          <div class="mb-3 space-y-1">
            <div v-if="result.product.brand" class="flex items-center text-sm text-gray-600">
              <i class="fas fa-tag mr-2 text-gray-400"></i>
              {{ result.product.brand.name }}
            </div>
            <div v-if="result.product.size" class="flex items-center text-sm text-gray-600">
              <i class="fas fa-ruler mr-2 text-gray-400"></i>
              Taille {{ result.product.size }}
            </div>
            <div v-if="result.product.condition" class="flex items-center text-sm text-gray-600">
              <i class="fas fa-check-circle mr-2 text-gray-400"></i>
              {{ result.product.condition.name }}
            </div>
          </div>

          <!-- Match Details -->
          <div class="match-details mb-3">
            <button
              @click.stop="toggleMatchDetails(result.product.id)"
              class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
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
                    class="bg-blue-100 text-blue-800 px-2 py-1 rounded"
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

          <!-- User and Actions -->
          <div class="flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-600">
              <img
                :src="result.product.user?.avatar_url || '/default-avatar.png'"
                :alt="result.product.user?.name"
                class="w-5 h-5 rounded-full mr-2"
              >
              <span>{{ result.product.user?.name }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <button
                @click.stop="toggleFavorite(result.product)"
                class="text-gray-400 hover:text-red-500 transition-colors"
                :class="{ 'text-red-500': result.product.is_favorited_by_user }"
              >
                <i class="fas fa-heart"></i>
              </button>
              <button
                @click.stop="toggleLike(result.product)"
                class="text-gray-400 hover:text-blue-500 transition-colors flex items-center"
                :class="{ 'text-blue-500': result.product.is_liked_by_user }"
              >
                <i class="fas fa-thumbs-up mr-1"></i>
                <span class="text-xs">{{ result.product.likes_count }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div v-if="canLoadMore" class="text-center mt-8">
      <button
        @click="$emit('load-more')"
        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
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
      if (score >= 60) return 'bg-yellow-500';
      if (score >= 40) return 'bg-orange-500';
      return 'bg-red-500';
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
    grid-template-columns: 1fr;
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .results-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
