<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Check if we're editing (has ID in route)
const isEditing = ref(false)
const itemId = ref(null)

// Form data
const formData = ref({
  item_name: '',
  ordering_unit_id: '',
  counting_unit_id: '',
  default_supplier_id: '',
  default_brand_id: '',
  group_id: '',
  latest_price: ''
})

// Options for dropdowns
const units = ref([])
const suppliers = ref([])
const brands = ref([])
const groups = ref([])

// Form validation and state
const errors = ref({})
const isLoading = ref(false)
const isSubmitting = ref(false)

onMounted(async () => {
  // Load dropdown options
  await Promise.all([
    loadUnits(),
    loadSuppliers(),
    loadBrands(),
    loadGroups()
  ])

  // Check if we have an ID in the route (editing mode)
  if (route.params.id) {
    isEditing.value = true
    itemId.value = route.params.id
    await loadItem()
  }
})

// Load dropdown options
const loadUnits = async () => {
  try {
    const response = await axios.get('/api/units')
    units.value = response.data.units.data || response.data.units || []
  } catch (error) {
    console.error('Error loading units:', error)
  }
}

const loadSuppliers = async () => {
  try {
    const response = await axios.get('/api/suppliers')
    suppliers.value = response.data.suppliers.data || response.data.suppliers || []
  } catch (error) {
    console.error('Error loading suppliers:', error)
  }
}

const loadBrands = async () => {
  try {
    const response = await axios.get('/api/brands')
    brands.value = response.data.brands || []
  } catch (error) {
    console.error('Error loading brands:', error)
  }
}

const loadGroups = async () => {
  try {
    const response = await axios.get('/api/groups')
    groups.value = response.data.groups.data || response.data.groups || []
  } catch (error) {
    console.error('Error loading groups:', error)
  }
}

// Load item data for editing
const loadItem = async () => {
  if (!itemId.value) return

  isLoading.value = true
  try {
    const response = await axios.get(`/api/items/${itemId.value}`)
    if (response.data && response.data.item) {
      // Populate form with existing data
      const item = response.data.item
      formData.value = {
        item_name: item.item_name || '',
        ordering_unit_id: item.ordering_unit_id || '',
        counting_unit_id: item.counting_unit_id || '',
        default_supplier_id: item.default_supplier_id || '',
        default_brand_id: item.default_brand_id || '',
        group_id: item.group_id || '',
        latest_price: item.latest_price || ''
      }
    }
  } catch (error) {
    console.error('Error loading item:', error)
    // Handle error (maybe redirect back to list)
  } finally {
    isLoading.value = false
  }
}

// Submit form
const submitForm = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errors.value = {}

  try {
    let response
    if (isEditing.value) {
      // Update existing item
      response = await axios.put(`/api/items/${itemId.value}`, formData.value)
    } else {
      // Create new item
      response = await axios.post('/api/items', formData.value)
    }

    if (response.data) {
      // Show success message (you might want to use a toast notification here)
      console.log(`Item ${isEditing.value ? 'updated' : 'created'} successfully:`, response.data.item)

      // Redirect to items list with a success message
      router.push({
        path: '/items',
        query: {
          success: isEditing.value ? 'updated' : 'created',
          item: response.data.item.item_name
        }
      })
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
    } else {
      console.error(`Error ${isEditing.value ? 'updating' : 'creating'} item:`, error)
      // Handle other errors (show general error message)
    }
  } finally {
    isSubmitting.value = false
  }
}

// Reset form
const resetForm = () => {
  formData.value = {
    item_name: '',
    ordering_unit_id: '',
    counting_unit_id: '',
    default_supplier_id: '',
    default_brand_id: '',
    group_id: '',
    latest_price: ''
  }
  errors.value = {}
}

// Helper function to check if field has error
const hasError = (field) => {
  return errors.value[field] && errors.value[field].length > 0
}

// Helper function to get error message
const getError = (field) => {
  return errors.value[field] ? errors.value[field][0] : ''
}
</script>

<template>
  <section class="section main-section m-0">
    <router-link to="/items" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Back</span>
    </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <div class="flex items-center justify-between w-full">
          <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-package-variant"></i></span>
            {{ isEditing ? 'Edit Item' : 'Add Item' }}
          </p>
        </div>
      </header>
      <div class="card-content">
        <div v-if="isLoading" class="text-center">
          <div class="button is-loading is-static">Loading item data...</div>
        </div>
        <form v-else @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Row 1: Item Name & Latest Price -->
            <div class="field">
              <label class="label">Item Name <span class="text-red-500">*</span></label>
              <div class="control">
                <input
                  class="input"
                  :class="{ 'border-red-500': hasError('item_name') }"
                  type="text"
                  placeholder="e.g. Tortilla Wraps"
                  v-model="formData.item_name">
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('item_name')">
                {{ getError('item_name') }}
              </p>
            </div>

            <div class="field">
              <label class="label">Latest Price</label>
              <div class="control">
                <input
                  class="input"
                  :class="{ 'border-red-500': hasError('latest_price') }"
                  type="number"
                  step="0.01"
                  placeholder="e.g. 15.99"
                  v-model="formData.latest_price">
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('latest_price')">
                {{ getError('latest_price') }}
              </p>
            </div>

            <!-- Row 2: Ordering Unit & Counting Unit -->
            <div class="field">
              <label class="label">Ordering Unit</label>
              <div class="control">
                <div class="select is-fullwidth" :class="{ 'border-red-500': hasError('ordering_unit_id') }">
                  <select v-model="formData.ordering_unit_id">
                    <option value="">Select Ordering Unit</option>
                    <option v-for="unit in units" :key="unit.id" :value="unit.id">
                      {{ unit.name }}
                    </option>
                  </select>
                </div>
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('ordering_unit_id')">
                {{ getError('ordering_unit_id') }}
              </p>
            </div>

            <div class="field">
              <label class="label">Counting Unit</label>
              <div class="control">
                <div class="select is-fullwidth" :class="{ 'border-red-500': hasError('counting_unit_id') }">
                  <select v-model="formData.counting_unit_id">
                    <option value="">Select Counting Unit</option>
                    <option v-for="unit in units" :key="unit.id" :value="unit.id">
                      {{ unit.name }}
                    </option>
                  </select>
                </div>
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('counting_unit_id')">
                {{ getError('counting_unit_id') }}
              </p>
            </div>

            <!-- Row 3: Default Supplier & Default Brand -->
            <div class="field">
              <label class="label">Default Supplier</label>
              <div class="control">
                <div class="select is-fullwidth" :class="{ 'border-red-500': hasError('default_supplier_id') }">
                  <select v-model="formData.default_supplier_id">
                    <option value="">Select Supplier</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                      {{ supplier.supplier_name }}
                    </option>
                  </select>
                </div>
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('default_supplier_id')">
                {{ getError('default_supplier_id') }}
              </p>
            </div>

            <div class="field">
              <label class="label">Default Brand</label>
              <div class="control">
                <div class="select is-fullwidth" :class="{ 'border-red-500': hasError('default_brand_id') }">
                  <select v-model="formData.default_brand_id">
                    <option value="">Select Brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
                </div>
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('default_brand_id')">
                {{ getError('default_brand_id') }}
              </p>
            </div>

            <!-- Row 4: Group (spans full width) -->
            <div class="field lg:col-span-2">
              <label class="label">Group</label>
              <div class="control">
                <div class="select is-fullwidth" :class="{ 'border-red-500': hasError('group_id') }">
                  <select v-model="formData.group_id">
                    <option value="">Select Group</option>
                    <option v-for="group in groups" :key="group.id" :value="group.id">
                      {{ group.name }}
                    </option>
                  </select>
                </div>
              </div>
              <p class="text-red-500 text-sm mt-1" v-if="hasError('group_id')">
                {{ getError('group_id') }}
              </p>
            </div>
          </div>

          <!-- Buttons -->
          <div class="border-t pt-6">
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                type="submit"
                class="button green flex-1 sm:flex-initial"
                :class="{ 'opacity-75 cursor-not-allowed': isSubmitting }"
                :disabled="isSubmitting">
                <span v-if="!isSubmitting">{{ isEditing ? 'Update Item' : 'Add Item' }}</span>
                <span v-else>{{ isEditing ? 'Updating...' : 'Adding...' }}</span>
              </button>

              <button
                type="button"
                class="button light flex-1 sm:flex-initial"
                @click="resetForm"
                :disabled="isSubmitting">
                Reset
              </button>

              <router-link
                to="/items"
                class="button is-outlined flex-1 sm:flex-initial"
                :class="{ 'opacity-75 pointer-events-none': isSubmitting }">
                Cancel
              </router-link>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
