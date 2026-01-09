<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
          Application History
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="space-y-4">
        <div v-if="loading" class="text-center py-4">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Loading history...</p>
        </div>

        <div v-else-if="!history || history.length === 0" class="text-center py-4">
          <p class="text-gray-600 dark:text-gray-400">No history records found</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="record in history"
            :key="record.id"
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
          >
            <div class="flex justify-between items-start">
              <div>
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(record.status)"
                >
                  {{ record.status }}
                </span>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                  By {{ record.admin?.name || 'Unknown' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                  {{ new Date(record.processed_at).toLocaleString() }}
                </p>
              </div>
            </div>
            <div v-if="record.feedback" class="mt-3">
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ record.feedback }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useCreatorApplicationsStore } from '@/stores/creator-applications'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  applicationId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close'])
const store = useCreatorApplicationsStore()
const history = ref([])
const loading = ref(false)

const fetchHistory = async () => {
  loading.value = true
  console.log('Fetching history for application:', props.applicationId)
  try {
    const response = await store.getApplicationHistory(props.applicationId)
    console.log('History response:', response)
    history.value = response
    console.log('Updated history value:', history.value)
  } catch (error) {
    console.error('Failed to fetch history:', error)
  } finally {
    loading.value = false
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
  }
  return classes[status] || ''
}

onMounted(() => {
  console.log('Component mounted, show:', props.show, 'applicationId:', props.applicationId)
  if (props.show) {
    fetchHistory()
  }
})

watch(() => props.show, (newValue) => {
  console.log('Show prop changed:', newValue, 'applicationId:', props.applicationId)
  if (newValue) {
    fetchHistory()
  }
})
</script> 