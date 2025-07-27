<template>
  <transition name="flash-message">
    <div
      v-if="visible"
      :class="[
        'notification flash-message',
        typeClasses
      ]"
    >
      <button class="delete" @click="hide"></button>
      <div class="media">
        <div class="media-left">
          <span class="icon is-large" :class="iconClasses">
            <i :class="iconClass"></i>
          </span>
        </div>
        <div class="media-content">
          <p class="title is-6">{{ title }}</p>
          <p v-if="message" class="subtitle is-7">{{ message }}</p>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  duration: {
    type: Number,
    default: 5000 // 5 seconds
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['hide'])

const visible = ref(false)
let timer = null

const typeClasses = computed(() => {
  const classes = {
    success: 'is-success',
    error: 'is-danger',
    warning: 'is-warning',
    info: 'is-info'
  }
  return classes[props.type]
})

const iconClasses = computed(() => {
  const classes = {
    success: 'has-text-success',
    error: 'has-text-danger',
    warning: 'has-text-warning',
    info: 'has-text-info'
  }
  return classes[props.type]
})

const iconClass = computed(() => {
  const icons = {
    success: 'mdi mdi-check-circle',
    error: 'mdi mdi-alert-circle',
    warning: 'mdi mdi-alert',
    info: 'mdi mdi-information'
  }
  return icons[props.type]
})

const hide = () => {
  visible.value = false
  if (timer) {
    clearTimeout(timer)
    timer = null
  }
  emit('hide')
}

const show = () => {
  visible.value = true
  if (props.duration > 0) {
    timer = setTimeout(() => {
      hide()
    }, props.duration)
  }
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    show()
  } else {
    hide()
  }
}, { immediate: true })
</script>

<style scoped>
.flash-message {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
  max-width: 400px;
  min-width: 300px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Ensure solid backgrounds for different message types */
.flash-message.is-success {
  background-color: #1eb873;
  color: white;
}

.flash-message.is-danger {
  background-color: #f14668;
  color: white;
}

.flash-message.is-warning {
  background-color: #ffe08a;
  color: rgba(0, 0, 0, 0.7);
}

.flash-message.is-info {
  background-color: #3e8ed0;
  color: white;
}

/* Ensure delete button is visible */
.flash-message .delete {
  background-color: rgba(255, 255, 255, 0.3);
}

.flash-message .delete:hover {
  background-color: rgba(255, 255, 255, 0.5);
}

.flash-message-enter-active {
  transition: all 0.3s ease;
}

.flash-message-leave-active {
  transition: all 0.2s ease;
}

.flash-message-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.flash-message-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
