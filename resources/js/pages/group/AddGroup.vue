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
const groupId = ref(null)

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
    groupId.value = route.params.id
    await loadGroup()
  }
})

// Load group data for editing
const loadGroup = async () => {
  if (!groupId.value) return

  isLoading.value = true
  try {
    const response = await axios.get(`/api/groups/${groupId.value}`)
    if (response.data && response.data.group) {
      // Populate form with existing data
      const group = response.data.group
      formData.value = {
        name: group.name || ''
      }
    }
  } catch (error) {
    console.error('Error loading group:', error)
    error('Loading Error', 'Error loading group data. Please try again.')
    // Redirect back to list on error
    router.push('/groups')
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
      // Update existing group
      response = await axios.put(`/api/groups/${groupId.value}`, formData.value)
    } else {
      // Create new group
      response = await axios.post('/api/groups', formData.value)
    }

    if (response.data) {
      // Show success flash message
      success(
        `Group ${isEditing.value ? 'Updated' : 'Created'}`,
        `"${response.data.group.name}" has been ${isEditing.value ? 'updated' : 'created'} successfully.`
      )

      // Redirect to groups list
      router.push('/groups')
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors || {}
      error('Validation Error', 'Please check the form for errors.')
    } else {
      console.error(`Error ${isEditing.value ? 'updating' : 'creating'} group:`, error)
      // Handle other errors (show general error message)
      error(
        `Error ${isEditing.value ? 'Updating' : 'Creating'} Group`,
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
    <router-link to="/groups" class="button is-small">
            <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
            <span class="sm:hidden lg:inline">Back</span>
          </router-link>

    <div class="card mb-6">
      <header class="card-header">
        <div class="flex items-center justify-between w-full">
          <p class="card-header-title">
            <span class="icon"><i class="mdi mdi-folder-plus"></i></span>
            {{ isEditing ? 'Edit Group' : 'Add Group' }}
          </p>
        </div>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="text-center">
          <div class="button is-loading is-static">Loading group data...</div>
        </div>

        <form v-else @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 gap-6">
            <!-- Group Name -->
            <div class="field">
                <label class="label">Group Name <span class="text-red-500">*</span></label>
                <div class="control">
                    <input
                      class="input"
                      :class="{ 'border-red-500': hasError('name') }"
                      type="text"
                      placeholder="e.g. Beverages, Snacks, Desserts"
                      v-model="formData.name"
                      required>
                </div>
                <p class="text-red-500 text-sm mt-1" v-if="hasError('name')">
                    {{ getError('name') }}
                </p>
                <p class="text-gray-600 text-sm mt-1">
                    Enter a unique name for the group. This will be used to categorize items.
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
                <span v-if="!isSubmitting">{{ isEditing ? 'Update Group' : 'Add Group' }}</span>
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
