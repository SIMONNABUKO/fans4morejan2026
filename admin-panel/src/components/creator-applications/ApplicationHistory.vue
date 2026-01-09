<script setup>
const props = defineProps({
  application: {
    type: Object,
    required: true
  },
  history: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['close'])

const formatDate = (date) => {
  return new Date(date).toLocaleString()
}
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
          Application History
        </h3>
        <button
          @click="emit('close')"
          class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- User Info -->
      <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
        <div class="flex items-center">
          <img
            :src="application.user.avatar"
            :alt="application.user.name"
            class="h-12 w-12 rounded-full"
          >
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900 dark:text-white">
              {{ application.user.name }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ application.user.email }}
            </div>
          </div>
        </div>
      </div>

      <!-- History Timeline -->
      <div class="space-y-6">
        <div
          v-for="(event, index) in history"
          :key="index"
          class="relative pl-8 pb-6"
        >
          <!-- Timeline Line -->
          <div
            v-if="index !== history.length - 1"
            class="absolute left-3 top-3 -bottom-3 w-0.5 bg-gray-200 dark:bg-gray-700"
          ></div>

          <!-- Timeline Dot -->
          <div
            class="absolute left-0 top-0 w-6 h-6 rounded-full border-2 flex items-center justify-center"
            :class="[
              event.status === 'approved'
                ? 'border-green-500 bg-green-100 dark:bg-green-900/50'
                : event.status === 'rejected'
                  ? 'border-red-500 bg-red-100 dark:bg-red-900/50'
                  : 'border-yellow-500 bg-yellow-100 dark:bg-yellow-900/50'
            ]"
          >
            <svg
              v-if="event.status === 'approved'"
              class="w-3 h-3 text-green-500"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <svg
              v-else-if="event.status === 'rejected'"
              class="w-3 h-3 text-red-500"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <svg
              v-else
              class="w-3 h-3 text-yellow-500"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>

          <!-- Event Content -->
          <div class="ml-2">
            <div class="flex items-center text-sm">
              <span
                class="font-medium"
                :class="[
                  event.status === 'approved'
                    ? 'text-green-700 dark:text-green-300'
                    : event.status === 'rejected'
                      ? 'text-red-700 dark:text-red-300'
                      : 'text-yellow-700 dark:text-yellow-300'
                ]"
              >
                {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
              </span>
              <span class="mx-2 text-gray-400">•</span>
              <span class="text-gray-500 dark:text-gray-400">
                {{ formatDate(event.created_at) }}
              </span>
            </div>
            <p
              v-if="event.notes"
              class="mt-2 text-sm text-gray-600 dark:text-gray-300"
            >
              {{ event.notes }}
            </p>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              By: {{ event.admin.name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template> 