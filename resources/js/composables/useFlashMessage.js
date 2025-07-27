import { ref } from 'vue'

const flashMessages = ref([])
let messageId = 0

export const useFlashMessage = () => {
  const showMessage = (type, title, message = '', duration = 5000) => {
    const id = ++messageId
    const flashMessage = {
      id,
      type,
      title,
      message,
      duration,
      visible: true
    }

    flashMessages.value.push(flashMessage)

    // Auto-remove after duration
    if (duration > 0) {
      setTimeout(() => {
        removeMessage(id)
      }, duration)
    }

    return id
  }

  const removeMessage = (id) => {
    const index = flashMessages.value.findIndex(msg => msg.id === id)
    if (index > -1) {
      flashMessages.value.splice(index, 1)
    }
  }

  const clearAll = () => {
    flashMessages.value = []
  }

  // Convenience methods
  const success = (title, message = '', duration = 5000) => {
    return showMessage('success', title, message, duration)
  }

  const error = (title, message = '', duration = 7000) => {
    return showMessage('error', title, message, duration)
  }

  const warning = (title, message = '', duration = 6000) => {
    return showMessage('warning', title, message, duration)
  }

  const info = (title, message = '', duration = 5000) => {
    return showMessage('info', title, message, duration)
  }

  return {
    flashMessages,
    showMessage,
    removeMessage,
    clearAll,
    success,
    error,
    warning,
    info
  }
}
