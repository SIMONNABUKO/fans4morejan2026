<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Search -->
      <div>
        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
        <input
          type="text"
          id="search"
          v-model="filters.search"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
          placeholder="Search posts..."
        />
      </div>

      <!-- Status Filter -->
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
          id="status"
          v-model="filters.status"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="published">Published</option>
          <option value="rejected">Rejected</option>
          <option value="reported">Reported</option>
        </select>
      </div>

      <!-- Date Range -->
      <div>
        <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
        <input
          type="date"
          id="dateFrom"
          v-model="filters.dateFrom"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
        />
      </div>

      <!-- Report Status -->
      <div>
        <label for="reportStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Report Status</label>
        <select
          id="reportStatus"
          v-model="filters.reportStatus"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
        >
          <option value="">All Reports</option>
          <option value="reported">Reported</option>
          <option value="reviewed">Reviewed</option>
          <option value="resolved">Resolved</option>
        </select>
      </div>
    </div>

    <!-- Filter Actions -->
    <div class="mt-4 flex justify-end space-x-3">
      <button
        @click="resetFilters"
        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Reset
      </button>
      <button
        @click="applyFilters"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Apply Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialFilters: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:filters', 'apply'])

const filters = reactive({
  search: '',
  status: '',
  dateFrom: '',
  reportStatus: '',
  ...props.initialFilters
})

const resetFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  emit('update:filters', { ...filters })
  emit('apply')
}

const applyFilters = () => {
  emit('update:filters', { ...filters })
  emit('apply')
}

// Watch for changes in individual filters
watch(filters, (newFilters) => {
  emit('update:filters', { ...newFilters })
}, { deep: true })
</script> 