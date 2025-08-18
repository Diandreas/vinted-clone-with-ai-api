<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
              <form @submit.prevent="handleSubmit">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-100">
                    <PackageIcon class="h-6 w-6 text-primary-600" />
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                      {{ isEditing ? 'Modifier le produit' : 'Nouveau produit' }}
                    </DialogTitle>
                  </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                  <!-- Titre -->
                  <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700">
                      Titre *
                    </label>
                    <div class="mt-1">
                      <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        required
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Titre du produit"
                      />
                    </div>
                  </div>

                  <!-- Description -->
                  <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                      Description *
                    </label>
                    <div class="mt-1">
                      <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        required
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Description détaillée du produit"
                      ></textarea>
                    </div>
                  </div>

                  <!-- Catégorie -->
                  <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">
                      Catégorie *
                    </label>
                    <div class="mt-1">
                      <select
                        id="category_id"
                        v-model="form.category_id"
                        required
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="">Sélectionnez une catégorie</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                          {{ category.name }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Marque -->
                  <div>
                    <label for="brand_id" class="block text-sm font-medium text-gray-700">
                      Marque
                    </label>
                    <div class="mt-1">
                      <select
                        id="brand_id"
                        v-model="form.brand_id"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="">Sélectionnez une marque</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                          {{ brand.name }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Condition -->
                  <div>
                    <label for="condition_id" class="block text-sm font-medium text-gray-700">
                      État *
                    </label>
                    <div class="mt-1">
                      <select
                        id="condition_id"
                        v-model="form.condition_id"
                        required
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="">Sélectionnez un état</option>
                        <option v-for="condition in conditions" :key="condition.id" :value="condition.id">
                          {{ condition.name }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <!-- Prix -->
                  <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">
                      Prix (Fcfa) *
                    </label>
                    <div class="mt-1">
                      <input
                        id="price"
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="0.00"
                      />
                    </div>
                  </div>

                  <!-- Prix original -->
                  <div>
                    <label for="original_price" class="block text-sm font-medium text-gray-700">
                      Prix original (Fcfa)
                    </label>
                    <div class="mt-1">
                      <input
                        id="original_price"
                        v-model="form.original_price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="0.00"
                      />
                    </div>
                  </div>

                  <!-- Taille -->
                  <div>
                    <label for="size" class="block text-sm font-medium text-gray-700">
                      Taille
                    </label>
                    <div class="mt-1">
                      <input
                        id="size"
                        v-model="form.size"
                        type="text"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="S, M, L, XL..."
                      />
                    </div>
                  </div>

                  <!-- Couleur -->
                  <div>
                    <label for="color" class="block text-sm font-medium text-gray-700">
                      Couleur
                    </label>
                    <div class="mt-1">
                      <input
                        id="color"
                        v-model="form.color"
                        type="text"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Noir, Blanc, Rouge..."
                      />
                    </div>
                  </div>

                  <!-- Matière -->
                  <div>
                    <label for="material" class="block text-sm font-medium text-gray-700">
                      Matière
                    </label>
                    <div class="mt-1">
                      <input
                        id="material"
                        v-model="form.material"
                        type="text"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Coton, Polyester, Cuir..."
                      />
                    </div>
                  </div>

                  <!-- Statut -->
                  <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                      Statut
                    </label>
                    <div class="mt-1">
                      <select
                        id="status"
                        v-model="form.status"
                        class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="draft">Brouillon</option>
                        <option value="active">Actif</option>
                        <option value="sold">Vendu</option>
                        <option value="reserved">Réservé</option>
                        <option value="removed">Supprimé</option>
                      </select>
                    </div>
                  </div>

                  <!-- Options -->
                  <div class="sm:col-span-2">
                    <div class="space-y-4">
                      <div class="flex items-center">
                        <input
                          id="is_negotiable"
                          v-model="form.is_negotiable"
                          type="checkbox"
                          class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded"
                        />
                        <label for="is_negotiable" class="ml-2 block text-sm text-gray-900">
                          Prix négociable
                        </label>
                      </div>

                      <div class="flex items-center">
                        <input
                          id="is_featured"
                          v-model="form.is_featured"
                          type="checkbox"
                          class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded"
                        />
                        <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                          Produit mis en avant
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="error" class="mt-4 bg-gray-50 border border-gray-200 rounded-md p-4">
                  <div class="text-sm text-gray-800">
                    {{ error }}
                  </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm"
                  >
                    <LoaderIcon v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ loading ? 'Enregistrement...' : (isEditing ? 'Modifier' : 'Créer') }}
                  </button>
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm"
                  >
                    Annuler
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { PackageIcon, LoaderIcon } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  product: Object,
  categories: Array,
  brands: Array,
  conditions: Array
})

const emit = defineEmits(['close', 'saved'])

// State
const loading = ref(false)
const error = ref('')

const form = reactive({
  title: '',
  description: '',
  category_id: '',
  brand_id: '',
  condition_id: '',
  price: '',
  original_price: '',
  size: '',
  color: '',
  material: '',
  status: 'draft',
  is_negotiable: false,
  is_featured: false
})

// Computed
const isEditing = computed(() => !!props.product?.id)

// Methods
const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (typeof form[key] === 'boolean') {
      form[key] = false
    } else {
      form[key] = ''
    }
  })
  form.status = 'draft'
  error.value = ''
}

const populateForm = () => {
  if (props.product) {
    Object.keys(form).forEach(key => {
      if (props.product[key] !== undefined) {
        form[key] = props.product[key]
      }
    })
  }
}

const handleSubmit = async () => {
  if (loading.value) return

  loading.value = true
  error.value = ''

  try {
    const data = { ...form }

    // Convertir les chaînes vides en null pour les champs optionnels
    Object.keys(data).forEach(key => {
      if (data[key] === '' && ['brand_id', 'original_price', 'size', 'color', 'material'].includes(key)) {
        data[key] = null
      }
    })

    let response
    if (isEditing.value) {
      response = await window.axios.put(`/products/${props.product.id}`, data)
    } else {
      response = await window.axios.post('/products', data)
    }

    emit('saved', response.data)
    resetForm()
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join('\n')
    } else {
      error.value = err.response?.data?.message || 'Une erreur est survenue'
    }
  } finally {
    loading.value = false
  }
}

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    if (props.product) {
      populateForm()
    } else {
      resetForm()
    }
  }
})

watch(() => props.product, (newProduct) => {
  if (newProduct && props.show) {
    populateForm()
  }
})
</script>



