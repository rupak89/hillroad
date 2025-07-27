<template>
  <div v-if="show" class="modal is-active" style="z-index: 10000;">
    <div class="modal-background" @click="onCancel"></div>
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ title }}</p>
        <button class="delete" aria-label="close" @click="onCancel" :disabled="isLoading"></button>
      </header>
      <section class="modal-card-body">
        <div class="content">
          <div class="has-text-centered mb-4">
            <span class="icon is-large has-text-warning">
              <i class="mdi mdi-alert mdi-48px"></i>
            </span>
          </div>
          <p>{{ message }}</p>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button
          class="button is-danger"
          @click="onConfirm"
          :disabled="isLoading"
          :class="{ 'is-loading': isLoading }">
          {{ isLoading ? loadingText : confirmText }}
        </button>
        <button
          class="button"
          @click="onCancel"
          :disabled="isLoading">
          {{ cancelText }}
        </button>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed? This action cannot be undone.'
  },
  confirmText: {
    type: String,
    default: 'Delete'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loadingText: {
    type: String,
    default: 'Deleting...'
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const onConfirm = () => {
  emit('confirm')
}

const onCancel = () => {
  emit('cancel')
}

// Close modal on Escape key
const handleKeydown = (event) => {
  if (event.key === 'Escape' && props.show && !props.isLoading) {
    onCancel()
  }
}

watch(() => props.show, (newValue) => {
  console.log('ConfirmModal show prop changed to:', newValue);
  if (newValue) {
    document.addEventListener('keydown', handleKeydown)
    document.body.classList.add('is-clipped')
  } else {
    document.removeEventListener('keydown', handleKeydown)
    document.body.classList.remove('is-clipped')
  }
})
</script>
