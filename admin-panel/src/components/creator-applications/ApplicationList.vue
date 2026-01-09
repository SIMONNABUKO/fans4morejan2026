<script setup>
import { ref, computed } from 'vue'
import MediaPreview from '@/components/common/MediaPreview.vue'
import ApplicationHistoryModal from './ApplicationHistoryModal.vue'

const props = defineProps({
  applications: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  total: {
    type: Number,
    required: true
  },
  currentPage: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['page-change', 'status-update', 'view-history'])

const selectedApplication = ref(null)
const showStatusModal = ref(false)
const showHistoryModal = ref(false)
const statusNotes = ref('')
const showMediaPreview = ref(false)
const selectedMedia = ref(null)

const getVerificationFiles = (application) => {
  const files = {
    front_id: application.front_id,
    back_id: application.back_id,
    holding_id: application.holding_id,
    verification_sign: application.verification_sign,
    verification_video: application.verification_video
  }
  return Object.entries(files)
    .filter(([_, value]) => value)
    .map(([key, url]) => ({ key, url }))
}

const openStatusModal = (application) => {
  selectedApplication.value = application
  showStatusModal.value = true
}

const updateStatus = async (status) => {
  if (selectedApplication.value) {
    await emit('status-update', selectedApplication.value.id, status, statusNotes.value)
    showStatusModal.value = false
    selectedApplication.value = null
    statusNotes.value = ''
  }
}

const previewMedia = (url, type) => {
  selectedMedia.value = { url, type }
  showMediaPreview.value = true
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
  }
  return classes[status] || ''
}

const getMediaLabel = (key) => {
  const labels = {
    front_id: 'Front ID',
    back_id: 'Back ID',
    holding_id: 'Holding ID',
    verification_sign: 'Verification Sign',
    verification_video: 'Verification Video'
  }
  return labels[key] || key
}

const viewHistory = (application) => {
  selectedApplication.value = application
  showHistoryModal.value = true
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Loading State -->
    <div v-if="loading" class="p-8 text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
      <p class="mt-4 text-gray-600 dark:text-gray-400">Loading applications...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="applications.length === 0" class="p-8 text-center">
      <p class="text-gray-600 dark:text-gray-400">No applications found</p>
    </div>

    <!-- Applications Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Verification Files
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Applied On
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="application in applications" :key="application.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <img
                  :src="application.user.avatar"
                  :alt="application.user.name"
                  class="h-10 w-10 rounded-full"
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
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="getStatusClass(application.status)"
              >
                {{ application.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="space-y-2">
                <div
                  v-for="file in getVerificationFiles(application)"
                  :key="file.key"
                  class="flex items-center space-x-2"
                >
                  <button
                    @click="previewMedia(file.url, file.key === 'verification_video' ? 'video' : 'image')"
                    class="text-sm text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 flex items-center"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ getMediaLabel(file.key) }}
                  </button>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ new Date(application.created_at).toLocaleDateString() }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <button
                @click="openStatusModal(application)"
                class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3"
              >
                Update Status
              </button>
              <button
                @click="viewHistory(application)"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
              >
                View History
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} results
        </div>
        <div class="flex space-x-2">
          <button
            v-for="page in Math.ceil(total / perPage)"
            :key="page"
            @click="emit('page-change', page)"
            class="px-3 py-1 rounded-md"
            :class="[
              page === currentPage
                ? 'bg-primary-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div
      v-if="showStatusModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Update Application Status
        </h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Notes
            </label>
            <textarea
              v-model="statusNotes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white"
              placeholder="Add notes about this decision..."
            ></textarea>
          </div>
          <div class="flex justify-end space-x-3">
            <button
              @click="showStatusModal = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              Cancel
            </button>
            <button
              @click="updateStatus('approved')"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            >
              Approve
            </button>
            <button
              @click="updateStatus('rejected')"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Media Preview Modal -->
    <MediaPreview
      v-if="showMediaPreview"
      :media="selectedMedia"
      @close="showMediaPreview = false"
    />

    <!-- Add the History Modal -->
    <ApplicationHistoryModal
      v-if="showHistoryModal"
      :show="showHistoryModal"
      :application-id="selectedApplication?.id"
      @close="showHistoryModal = false"
    />
  </div>
</template> 