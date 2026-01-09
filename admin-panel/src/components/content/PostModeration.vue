<template>
  <div class="flex space-x-2">
    <!-- View Details Button -->
    <button
      @click="$emit('view', post)"
      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
    >
      <span class="sr-only">View</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>
    </button>

    <!-- Approve Button -->
    <button
      v-if="post.status === 'pending' || post.status === 'reported'"
      @click="handleModerate('approve')"
      class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
      :disabled="loading"
    >
      <span class="sr-only">Approve</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </button>

    <!-- Reject Button -->
    <button
      v-if="post.status === 'pending' || post.status === 'reported'"
      @click="handleModerate('reject')"
      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
      :disabled="loading"
    >
      <span class="sr-only">Reject</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Review Report Button -->
    <button
      v-if="post.status === 'reported'"
      @click="handleModerate('review')"
      class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
      :disabled="loading"
    >
      <span class="sr-only">Review Report</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>

    <!-- Loading Spinner -->
    <div v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-indigo-600"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['moderate', 'view'])

const loading = ref(false)

const handleModerate = async (action) => {
  loading.value = true
  try {
    await emit('moderate', { post: props.post, action })
  } finally {
    loading.value = false
  }
}
</script> 