<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'
import { useFlashMessage } from '@/composables/useFlashMessage.js'

const router = useRouter()
const route = useRoute()
const { success, error } = useFlashMessage()

// Check if we're editing (has ID in route)
const isEditing = ref(false)
const brandId = ref(null)

// Form data
const formData = ref({
  name: ''
})

// Form validation and state
const errors = ref({})
const isLoading = ref(false)
const isSubmitting = ref(false)

onMounted(async () => {
  // Check if we have an ID in the route (editing mode)
  if (route.params.id) {
    isEditing.value = true
    brandId.value = route.params.id
    await loadBrand()
  }
})

// Load brand data for editing
const loadBrand = async () => {
  if (!brandId.value) return

  isLoading.value = true
  try {
    const response = await axios.get(`/api/brands/${brandId.value}`)
    if (response.data && response.data.brand) {
      // Populate form with existing data
      const brand = response.data.brand
      formData.value = {
        name: brand.name || ''
      }
    }
  } catch (error) {
    console.error('Error loading brand:', error)
    error('Loading Error', 'Error loading brand data. Please try again.')
    // Redirect back to list on error
    router.push('/brands')
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
      // Update existing brand
      response = await axios.put(`/api/brands/${brandId.value}`, formData.value)
    } else {
      // Create new brand
      response = await axios.post('/api/brands', formData.value)
    }

    if (response.data) {
      // Show success flash message
      success(
        `Brand ${isEditing.value ? 'Updated' : 'Created'}`,
        `"${response.data.brand.name}" has been ${isEditing.value ? 'updated' : 'created'} successfully.`
      )

      // Redirect to brands list
      router.push('/brands')
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
      error('Validation Error', 'Please check the form for errors.')
    } else {
      console.error(`Error ${isEditing.value ? 'updating' : 'creating'} brand:`, error)
      // Handle other errors (show general error message)
      error(
        `Error ${isEditing.value ? 'Updating' : 'Creating'} Brand`,
        'An unexpected error occurred. Please try again.'
      )
    }
  } finally {
    isSubmitting.value = false
  }
}

// Reset form
const resetForm = () => {
  formData.value = {
    name: ''
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
  <section class="section main-section m-0" >
    <router-link to="/brands" class="button is-small">
            <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
            <span class="sm:hidden lg:inline">Back</span>
          </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <div class="flex items-center justify-between w-full">
          <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-tag-plus"></i></span>
            {{ isEditing ? 'Edit Brand' : 'Add Brand' }}
          </p>
        </div>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="text-center">
          <div class="button is-loading is-static">Loading brand data...</div>
        </div>

        <form v-else @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 gap-6">
            <!-- Brand Name -->
            <div class="field">
                <label class="label">Brand Name <span class="text-red-500">*</span></label>
                <div class="control">
                    <input
                      class="input"
                      :class="{ 'border-red-500': hasError('name') }"
                      type="text"
                      placeholder="e.g. Coca-Cola, Nike, Apple"
                      v-model="formData.name"
                      required>
                </div>
                <p class="text-red-500 text-sm mt-1" v-if="hasError('name')">
                    {{ getError('name') }}
                </p>
                <p class="text-gray-600 text-sm mt-1">
                    Enter a unique name for the brand. This will be used to identify products by their manufacturer or brand.
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
                <span v-if="!isSubmitting">{{ isEditing ? 'Update Brand' : 'Add Brand' }}</span>
                <span v-else>{{ isEditing ? 'Updating...' : 'Adding...' }}</span>
              </button>

              <button
                type="button"
                class="button light flex-1 sm:flex-initial"
                @click="resetForm"
                :disabled="isSubmitting">
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
