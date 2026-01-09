<!-- ResponsiveTable.vue -->
<script setup lang="ts">
import { computed } from 'vue'

interface Column {
  key: string
  label: string
  sortable?: boolean
  priority?: number // Priority for mobile view (1 = highest, show first)
  formatter?: (value: any) => string
}

interface Props {
  columns: Column[]
  data: any[]
  loading?: boolean
  mobileBreakpoint?: number
  showMobileCards?: boolean // Whether to show card view on mobile
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  mobileBreakpoint: 768,
  showMobileCards: true
})

// Sort columns by priority for mobile view
const prioritizedColumns = computed(() => {
  return [...props.columns].sort((a, b) => {
    const priorityA = a.priority || 999
    const priorityB = b.priority || 999
    return priorityA - priorityB
  })
})

// Format cell value if formatter is provided
const formatCellValue = (column: Column, value: any) => {
  if (column.formatter) {
    return column.formatter(value)
  }
  return value
}
</script>

<template>
  <div class="w-full overflow-hidden">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-600"></div>
    </div>

    <div v-else>
      <!-- Desktop Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ column.label }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="(row, index) in data" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td
                v-for="column in columns"
                :key="column.key"
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
              >
                {{ formatCellValue(column, row[column.key]) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card View -->
      <div class="md:hidden space-y-4">
        <div
          v-for="(row, index) in data"
          :key="index"
          class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-3"
        >
          <div
            v-for="column in prioritizedColumns"
            :key="column.key"
            class="flex justify-between items-center text-sm"
          >
            <span class="font-medium text-gray-500 dark:text-gray-400">{{ column.label }}</span>
            <span class="text-gray-900 dark:text-gray-100">
              {{ formatCellValue(column, row[column.key]) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="data.length === 0"
        class="text-center py-8 text-gray-500 dark:text-gray-400"
      >
        <slot name="empty">
          No data available
        </slot>
      </div>
    </div>
  </div>
</template>

<style scoped>
.table-scroll {
  @apply overflow-x-auto;
  -webkit-overflow-scrolling: touch;
}

@media (max-width: 768px) {
  .table-scroll {
    @apply -mx-4;
  }
}
</style> 