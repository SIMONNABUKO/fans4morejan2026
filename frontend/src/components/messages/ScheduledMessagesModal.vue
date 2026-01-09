<template>
  <Modal v-if="isOpen" @close="closeModal">
    <div class="w-full">
      <h3 class="text-lg font-medium leading-6 text-text-light-primary dark:text-text-dark-primary mb-4">
        Scheduled Messages
      </h3>
      <div class="mb-4">
        <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
          Your scheduled messages will appear here.
        </p>
      </div>

      <div class="mb-4">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search scheduled messages"
          class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-md focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary"
        />
      </div>

      <div class="max-h-60 overflow-y-auto mb-4">
        <ul class="divide-y divide-border-light dark:divide-border-dark">
          <li v-for="(message, index) in filteredMessages" :key="index" class="py-4">
            <div class="flex items-center space-x-4">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary truncate">
                  {{ message.recipient }}
                </p>
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary truncate">
                  {{ message.content }}
                </p>
              </div>
              <div class="inline-flex items-center text-sm text-text-light-tertiary dark:text-text-dark-tertiary">
                {{ message.scheduledTime }}
              </div>
            </div>
          </li>
        </ul>
      </div>

      <div class="flex justify-between">
        <button
          type="button"
          class="inline-flex justify-center rounded-md border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark px-4 py-2 text-sm font-medium text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
          @click="closeModal"
        >
          Close
        </button>
        <button
          type="button"
          class="inline-flex justify-center rounded-md border border-transparent bg-primary-light dark:bg-primary-dark px-4 py-2 text-sm font-medium text-white hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
          @click="createNewScheduledMessage"
        >
          New Scheduled Message
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import Modal from '@/components/common/Modal.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close'])

const closeModal = () => {
  emit('close')
}

const createNewScheduledMessage = () => {
  // Implement the logic to create a new scheduled message
  console.log('Create new scheduled message')
}

// Mock data for scheduled messages
const scheduledMessages = ref([
  { recipient: 'John Doe', content: 'Meeting reminder', scheduledTime: '2023-05-20 10:00 AM' },
  { recipient: 'Jane Smith', content: 'Project update', scheduledTime: '2023-05-21 2:00 PM' },
  { recipient: 'Mike Johnson', content: 'Birthday wishes', scheduledTime: '2023-05-22 9:00 AM' },
])

const searchQuery = ref('')

const filteredMessages = computed(() => {
  if (!searchQuery.value) return scheduledMessages.value
  const query = searchQuery.value.toLowerCase()
  return scheduledMessages.value.filter(message => 
    message.recipient.toLowerCase().includes(query) ||
    message.content.toLowerCase().includes(query)
  )
})
</script>

