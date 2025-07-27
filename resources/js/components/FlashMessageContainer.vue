<template>
  <div class="fixed top-4 right-4 z-50 ">
    <FlashMessage
      v-for="message in flashMessages"
      :key="message.id"
      :type="message.type"
      :title="message.title"
      :message="message.message"
      :show="message.visible"
      @hide="removeMessage(message.id)"
    />
  </div>
</template>

<script setup>
import FlashMessage from './FlashMessage.vue'
import { useFlashMessage } from '../composables/useFlashMessage.js'

const { flashMessages, removeMessage } = useFlashMessage()
</script>
