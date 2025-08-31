<template>
  <div>
    <!-- Status Badge -->
    <div v-if="showStatusBadge" class="inline-block">
      <span :class="['status-badge', statusClass]">
        {{ statusText }}
      </span>
    </div>
    
    <!-- Unavailable Overlay -->
    <div 
      v-if="isUnavailable && showOverlay" 
      class="unavailable-overlay"
      :class="overlayClass"
    >
      <div class="overlay-content">
        <div class="overlay-text">{{ overlayText }}</div>
        <div v-if="showDescription" class="overlay-description">{{ overlayDescription }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  showStatusBadge: {
    type: Boolean,
    default: true
  },
  showOverlay: {
    type: Boolean,
    default: false
  },
  showDescription: {
    type: Boolean,
    default: false
  },
  overlayClass: {
    type: String,
    default: ''
  }
})

// Computed properties
const isUnavailable = computed(() => {
  if (!props.product) return false
  return ['sold', 'removed', 'inactive'].includes(props.product.status)
})

const statusClass = computed(() => {
  if (!props.product) return ''
  
  switch (props.product.status) {
    case 'sold':
      return 'status-sold'
    case 'removed':
      return 'status-removed'
    case 'inactive':
      return 'status-inactive'
    case 'reserved':
      return 'status-reserved'
    default:
      return 'status-active'
  }
})

const statusText = computed(() => {
  if (!props.product) return ''
  
  switch (props.product.status) {
    case 'sold':
      return 'Vendu'
    case 'removed':
      return 'Supprimé'
    case 'inactive':
      return 'Désactivé'
    case 'reserved':
      return 'Réservé'
    default:
      return 'Actif'
  }
})

const overlayText = computed(() => {
  if (!props.product) return ''
  
  switch (props.product.status) {
    case 'sold':
      return 'Vendu'
    case 'removed':
      return 'Supprimé'
    case 'inactive':
      return 'Désactivé'
    case 'reserved':
      return 'Réservé'
    default:
      return 'Indisponible'
  }
})

const overlayDescription = computed(() => {
  if (!props.product) return ''
  
  switch (props.product.status) {
    case 'sold':
      return 'Ce produit a été vendu'
    case 'removed':
      return 'Ce produit a été supprimé'
    case 'inactive':
      return 'Ce produit est désactivé'
    case 'reserved':
      return 'Ce produit est réservé'
    default:
      return 'Ce produit n\'est plus disponible'
  }
})

// Utility functions for external use
const isProductUnavailable = (product) => {
  if (!product) return false
  return ['sold', 'removed', 'inactive'].includes(product.status)
}

const getProductStatusClass = (product) => {
  if (!product) return ''
  
  switch (product.status) {
    case 'sold':
      return 'status-sold'
    case 'removed':
      return 'status-removed'
    case 'inactive':
      return 'status-inactive'
    case 'reserved':
      return 'status-reserved'
    default:
      return 'status-active'
  }
}

const getProductStatusText = (product) => {
  if (!product) return ''
  
  switch (product.status) {
    case 'sold':
      return 'Vendu'
    case 'removed':
      return 'Supprimé'
    case 'inactive':
      return 'Désactivé'
    case 'reserved':
      return 'Réservé'
    default:
      return 'Actif'
  }
}

// Expose utility functions
defineExpose({
  isProductUnavailable,
  getProductStatusClass,
  getProductStatusText
})
</script>

<style scoped>
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-sold {
  background: #ffebee;
  color: #f44336;
}

.status-removed {
  background: #ffebee;
  color: #f44336;
}

.status-inactive {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-reserved {
  background: #fff3e0;
  color: #ff9800;
}

.status-active {
  background: #e8f5e8;
  color: #4caf50;
}

.unavailable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 1;
}

.overlay-content {
  text-align: center;
}

.overlay-text {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 8px;
}

.overlay-description {
  font-size: 14px;
  color: #ccc;
}
</style>




