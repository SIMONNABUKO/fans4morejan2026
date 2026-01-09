<template>
  <div class="industry-chart">
    <!-- Chart Header -->
    <div v-if="showHeader" class="chart-header mb-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ subtitle }}
          </p>
        </div>
        <div v-if="showActions" class="flex items-center space-x-2">
          <button
            v-if="showFullscreen"
            @click="toggleFullscreen"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :title="isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'"
          >
            <i :class="isFullscreen ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line'" class="text-gray-600 dark:text-gray-400"></i>
          </button>
          <button
            v-if="showDownload"
            @click="downloadChart"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            title="Download chart"
          >
            <i class="ri-download-line text-gray-600 dark:text-gray-400"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="chart-loading">
      <div class="animate-pulse">
        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
        <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="isEmpty" class="chart-empty">
      <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400">
        <i class="ri-bar-chart-line text-4xl mb-2"></i>
        <p class="text-lg font-medium mb-1">{{ emptyTitle || 'No data available' }}</p>
        <p class="text-sm">{{ emptyMessage || 'Data will appear here when available.' }}</p>
      </div>
    </div>

    <!-- Chart Container -->
    <div
      v-else
      ref="chartContainer"
      class="chart-container"
      :class="{
        'fixed inset-0 z-50 bg-white dark:bg-gray-900 p-8': isFullscreen,
        'relative': !isFullscreen
      }"
    >
      <VueApexCharts
        ref="chartRef"
        :width="chartWidth"
        :height="chartHeight"
        :type="chartConfig.chart.type"
        :options="chartConfig"
        :series="chartSeries"
        @dataPointSelection="handleDataPointSelection"
        @legendClick="handleLegendClick"
      />
    </div>

    <!-- Chart Summary -->
    <div v-if="showSummary && summaryData" class="chart-summary mt-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="(item, index) in summaryData" :key="index" class="text-center">
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatSummaryValue(item.value, item.type) }}
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ item.label }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { useChartConfig } from '@/composables/useChartConfig'

const props = defineProps({
  // Chart type and data
  type: {
    type: String,
    default: 'line',
    validator: (value) => ['line', 'area', 'bar', 'pie', 'donut', 'mixed'].includes(value)
  },
  series: {
    type: Array,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  },
  
  // Chart appearance
  title: String,
  subtitle: String,
  height: {
    type: [String, Number],
    default: 350
  },
  width: {
    type: [String, Number],
    default: '100%'
  },
  colorScheme: {
    type: String,
    default: 'revenue'
  },
  
  // Chart options
  currency: Boolean,
  gradient: Boolean,
  horizontal: Boolean,
  showDataLabels: Boolean,
  showLegend: {
    type: Boolean,
    default: true
  },
  
  // UI features
  showHeader: {
    type: Boolean,
    default: true
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showFullscreen: {
    type: Boolean,
    default: true
  },
  showDownload: {
    type: Boolean,
    default: true
  },
  showSummary: Boolean,
  
  // States
  loading: Boolean,
  emptyTitle: String,
  emptyMessage: String,
  
  // Data
  summaryData: Array,
  
  // Custom options override
  customOptions: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['dataPointClick', 'legendClick', 'chartReady'])

const { 
  getLineChartConfig, 
  getAreaChartConfig, 
  getBarChartConfig, 
  getPieChartConfig, 
  getDonutChartConfig,
  getMixedChartConfig,
  getColorScheme 
} = useChartConfig()

// Refs
const chartRef = ref()
const chartContainer = ref()
const isFullscreen = ref(false)

// Computed properties
const isEmpty = computed(() => {
  return !props.series || props.series.length === 0 || 
    props.series.every(s => !s.data || s.data.length === 0)
})

const chartWidth = computed(() => {
  return isFullscreen.value ? '100%' : props.width
})

const chartHeight = computed(() => {
  return isFullscreen.value ? '80vh' : props.height
})

const chartConfig = computed(() => {
  let config = {}
  
  const options = {
    height: chartHeight.value,
    currency: props.currency,
    gradient: props.gradient,
    horizontal: props.horizontal,
    showDataLabels: props.showDataLabels,
    categories: props.categories,
    showLegend: props.showLegend
  }
  
  switch (props.type) {
    case 'line':
      config = getLineChartConfig(options)
      break
    case 'area':
      config = getAreaChartConfig(options)
      break
    case 'bar':
      config = getBarChartConfig(options)
      break
    case 'pie':
      config = getPieChartConfig(options)
      break
    case 'donut':
      config = getDonutChartConfig(options)
      break
    case 'mixed':
      config = getMixedChartConfig(options)
      break
    default:
      config = getLineChartConfig(options)
  }
  
  // Apply color scheme
  if (config.colors) {
    config.colors = getColorScheme(props.colorScheme)
  }
  
  // Merge custom options
  return mergeDeep(config, props.customOptions)
})

const chartSeries = computed(() => {
  if (!props.series) return []
  
  // Apply colors from color scheme
  const colors = getColorScheme(props.colorScheme)
  return props.series.map((series, index) => ({
    ...series,
    color: colors[index % colors.length]
  }))
})

// Methods
const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
  
  // Handle body scroll
  if (isFullscreen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const downloadChart = () => {
  if (chartRef.value) {
    chartRef.value.dataURI().then((uri) => {
      const link = document.createElement('a')
      link.href = uri.imgURI
      link.download = `${props.title || 'chart'}.png`
      link.click()
    })
  }
}

const handleDataPointSelection = (event, chartContext, config) => {
  emit('dataPointClick', {
    event,
    seriesIndex: config.seriesIndex,
    dataPointIndex: config.dataPointIndex,
    selectedDataPoints: config.selectedDataPoints
  })
}

const handleLegendClick = (chartContext, seriesIndex, config) => {
  emit('legendClick', {
    seriesIndex,
    config
  })
}

const formatSummaryValue = (value, type) => {
  switch (type) {
    case 'currency':
      return `$${parseFloat(value || 0).toFixed(2)}`
    case 'percentage':
      return `${parseFloat(value || 0).toFixed(1)}%`
    case 'number':
      if (value >= 1000000) {
        return `${(value / 1000000).toFixed(1)}M`
      }
      if (value >= 1000) {
        return `${(value / 1000).toFixed(1)}k`
      }
      return value.toString()
    default:
      return value.toString()
  }
}

// Utility function to deep merge objects
const mergeDeep = (target, source) => {
  const result = { ...target }
  
  for (const key in source) {
    if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
      result[key] = mergeDeep(result[key] || {}, source[key])
    } else {
      result[key] = source[key]
    }
  }
  
  return result
}

// Handle escape key for fullscreen exit
const handleKeydown = (event) => {
  if (event.key === 'Escape' && isFullscreen.value) {
    toggleFullscreen()
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  emit('chartReady', chartRef.value)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
  if (isFullscreen.value) {
    document.body.style.overflow = ''
  }
})

// Watchers
watch(() => props.series, () => {
  // Force chart refresh when series data changes
  if (chartRef.value) {
    chartRef.value.refresh()
  }
}, { deep: true })
</script>

<style scoped>
.industry-chart {
  @apply w-full;
}

.chart-header {
  @apply border-b border-gray-200 dark:border-gray-700 pb-4;
}

.chart-container {
  @apply bg-white dark:bg-gray-800 rounded-lg;
  min-height: 200px;
}

.chart-loading {
  @apply p-6;
}

.chart-empty {
  @apply bg-gray-50 dark:bg-gray-800 rounded-lg;
}

.chart-summary {
  @apply bg-gray-50 dark:bg-gray-800 rounded-lg p-4;
}

/* Fullscreen styles */
.industry-chart:has(.chart-container.fixed) {
  @apply relative;
}

/* ApexCharts custom styles */
:deep(.apexcharts-menu) {
  @apply bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg;
}

:deep(.apexcharts-menu-item) {
  @apply text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700;
}

:deep(.apexcharts-tooltip) {
  @apply bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg;
}

:deep(.apexcharts-tooltip-title) {
  @apply bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600;
}
</style> 