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
const supplierId = ref(null)

// Form data
const formData = ref({
  supplier_name: '',
  customer_number: '',
  contact_name: '',
  email: '',
  address: '',
  city: '',
  post_code: '',
  phone: '',
  website: ''
})

// Form validation and state
const errors = ref({})
const isLoading = ref(false)
const isSubmitting = ref(false)

onMounted(async () => {
  // Check if we have an ID in the route (editing mode)
  if (route.params.id) {
    isEditing.value = true
    supplierId.value = route.params.id
    await loadSupplier()
  }
})

// Load supplier data for editing
const loadSupplier = async () => {
  if (!supplierId.value) return

  isLoading.value = true
  try {
    const response = await axios.get(`/api/suppliers/${supplierId.value}`)
    if (response.data && response.data.supplier) {
      // Populate form with existing data
      const supplier = response.data.supplier
      formData.value = {
        supplier_name: supplier.supplier_name || '',
        customer_number: supplier.customer_number || '',
        contact_name: supplier.contact_name || '',
        email: supplier.email || '',
        address: supplier.address || '',
        city: supplier.city || '',
        post_code: supplier.post_code || '',
        phone: supplier.phone || '',
        website: supplier.website || ''
      }
    }
  } catch (error) {
    console.error('Error loading supplier:', error)
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
      // Update existing supplier
      response = await axios.put(`/api/suppliers/${supplierId.value}`, formData.value)
    } else {
      // Create new supplier
      response = await axios.post('/api/suppliers', formData.value)
    }

    if (response.data) {
      // Show success flash message
      success(
        `Supplier ${isEditing.value ? 'Updated' : 'Created'}`,
        `"${response.data.supplier.supplier_name}" has been ${isEditing.value ? 'updated' : 'created'} successfully.`
      )

      // Redirect to suppliers list
      router.push('/suppliers')
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
      error('Validation Error', 'Please check the form for errors.')
    } else {
      console.error(`Error ${isEditing.value ? 'updating' : 'creating'} supplier:`, error)
      // Handle other errors (show general error message)
      error(
        `Error ${isEditing.value ? 'Updating' : 'Creating'} Supplier`,
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
    supplier_name: '',
    customer_number: '',
    contact_name: '',
    email: '',
    address: '',
    city: '',
    post_code: '',
    phone: '',
    website: ''
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
    <router-link to="/suppliers" class="button is-small">
            <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
            <span class="sm:hidden lg:inline">Back</span>
          </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <div class="flex items-center justify-between w-full">
          <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-ballot"></i></span>
            {{ isEditing ? 'Edit Supplier' : 'Add Supplier' }}
          </p>

        </div>
      </header>
      <div class="card-content">
        <div v-if="isLoading" class="text-center">
          <div class="button is-loading is-static">Loading supplier data...</div>
        </div>
        <form v-else @submit.prevent="submitForm" class="space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Row 1: Supplier Name & Customer Number -->
                <div class="field">
                    <label class="label">Supplier Name <span class="text-red-500">*</span></label>
                    <div class="control">
                        <input
                          class="input"
                          :class="{ 'border-red-500': hasError('supplier_name') }"
                          type="text"
                          placeholder="e.g. bidfood"
                          v-model="formData.supplier_name"
                          required>
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('supplier_name')">
                        {{ getError('supplier_name') }}
                    </p>
                </div>

                <div class="field">
                    <label class="label">Customer Number</label>
                    <div class="control">
                        <input
                          class="input"
                          :class="{ 'border-red-500': hasError('customer_number') }"
                          type="text"
                          placeholder="e.g. SL-12345"
                          v-model="formData.customer_number">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('customer_number')">
                        {{ getError('customer_number') }}
                    </p>
                </div>

                <!-- Row 2: Contact Name & Email -->

                <div class="field">
                    <label class="label">Contact Name</label>
                    <div class="control">
                        <input
                        class="input"
                        :class="{ 'border-red-500': hasError('contact_name') }"
                        type="text"
                        placeholder="e.g. John Doe"
                        v-model="formData.contact_name">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('contact_name')">
                        {{ getError('contact_name') }}
                    </p>
                </div>

                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input
                        class="input"
                        :class="{ 'border-red-500': hasError('email') }"
                        type="email"
                        placeholder="e.g. contact@supplier.com"
                        v-model="formData.email">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('email')">
                        {{ getError('email') }}
                    </p>
                </div>

                <!-- Row 3: Address & Phone -->
                <div class="field">
                    <label class="label">Address</label>
                    <div class="control">
                        <input
                        class="input"
                        :class="{ 'border-red-500': hasError('address') }"
                        type="text"
                        placeholder="e.g. Address Line 1"
                        v-model="formData.address">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('address')">
                        {{ getError('address') }}
                    </p>
                </div>

                <div class="field">
                    <label class="label">Phone</label>
                    <div class="control">
                        <input
                        class="input"
                        :class="{ 'border-red-500': hasError('phone') }"
                        type="tel"
                        placeholder="e.g. 021 123 4567"
                        v-model="formData.phone">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('phone')">
                        {{ getError('phone') }}
                    </p>
                </div>
                <!-- Row 4: City & Post Code -->
                <div class="field">
                <label class="label">City</label>
                <div class="control">
                    <input
                      class="input"
                          :class="{ 'border-red-500': hasError('city') }"
                          type="text"
                          placeholder="e.g. Napier"
                          v-model="formData.city">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('city')">
                        {{ getError('city') }}
                    </p>
                </div>

                <div class="field">
                    <label class="label">Post Code</label>
                    <div class="control">
                        <input
                          class="input"
                          :class="{ 'border-red-500': hasError('post_code') }"
                          type="text"
                          placeholder="e.g. 4112"
                          v-model="formData.post_code">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('post_code')">
                        {{ getError('post_code') }}
                    </p>
                </div>

                <!-- Row 5: Website -->

                <div class="field">
                    <label class="label">Website</label>
                    <div class="control">
                        <input
                          class="input"
                          :class="{ 'border-red-500': hasError('website') }"
                          type="domain"
                          placeholder="e.g. https://supplier.com"
                          v-model="formData.website">
                    </div>
                    <p class="text-red-500 text-sm mt-1" v-if="hasError('website')">
                        {{ getError('website') }}
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
                <span v-if="!isSubmitting">{{ isEditing ? 'Update Supplier' : 'Add Supplier' }}</span>
                <span v-else>{{ isEditing ? 'Updating...' : 'Adding...' }}</span>
              </button>

            </div>
          </div>
        </form>
      </div>
    </div>

  </section>



</template>

