<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCreatorApplicationsStore } from '@/stores/creator-applications'
import ApplicationFilters from '@/components/creator-applications/ApplicationFilters.vue'
import ApplicationList from '@/components/creator-applications/ApplicationList.vue'
import ApplicationHistory from '@/components/creator-applications/ApplicationHistory.vue'

const store = useCreatorApplicationsStore()
const selectedApplication = ref(null)
const showHistory = ref(false)
const historyData = ref([])

const loading = computed(() => store.loading)
const error = computed(() => store.error)
const applications = computed(() => store.applications)
const totalApplications = computed(() => store.totalApplications)
const currentPage = computed(() => store.currentPage)
const perPage = computed(() => store.perPage)

const handleFilterChange = async (filters) => {
  store.setFilters(filters)
  await store.fetchApplications()
}

const handlePageChange = async (page) => {
  store.setPage(page)
  await store.fetchApplications()
}

const handleStatusUpdate = async (applicationId, status, notes) => {
  try {
    await store.updateApplicationStatus(applicationId, status, notes)
  } catch (error) {
    console.error('Failed to update status:', error)
  }
}

const viewHistory = async (application) => {
  selectedApplication.value = application
  showHistory.value = true
  try {
    historyData.value = await store.getApplicationHistory(application.id)
  } catch (error) {
    console.error('Failed to fetch history:', error)
  }
}

onMounted(async () => {
  await store.fetchApplications()
})
</script>

<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Creator Applications</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Review and manage creator applications
      </p>
    </div>

    <!-- Filters -->
    <ApplicationFilters
      @filter-change="handleFilterChange"
      :loading="loading"
    />

    <!-- Error Message -->
    <div
      v-if="error"
      class="mt-4 p-4 bg-red-50 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg"
    >
      {{ error }}
    </div>

    <!-- Applications List -->
    <ApplicationList
      :applications="applications"
      :loading="loading"
      :total="totalApplications"
      :current-page="currentPage"
      :per-page="perPage"
      @page-change="handlePageChange"
      @status-update="handleStatusUpdate"
      @view-history="viewHistory"
    />

    <!-- History Modal -->
    <ApplicationHistory
      v-if="showHistory"
      :application="selectedApplication"
      :history="historyData"
      @close="showHistory = false"
    />
  </div>
</template> 