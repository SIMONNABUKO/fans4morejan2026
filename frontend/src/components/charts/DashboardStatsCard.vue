<template>
  <div class="dashboard-stats-card bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <div 
          class="p-3 rounded-lg"
          :class="iconBgColor"
        >
          <i :class="icon" class="text-xl text-white"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-sm text-gray-600 dark:text-gray-400">
            {{ subtitle }}
          </p>
        </div>
      </div>
      
      <!-- Action Button -->
      <button 
        v-if="showAction"
        @click="$emit('action')"
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
      >
        <i class="ri-arrow-right-line text-gray-600 dark:text-gray-400"></i>
      </button>
    </div>

    <!-- Main Value -->
    <div class="mb-4">
      <div class="flex items-baseline space-x-3">
        <span class="text-3xl font-bold text-gray-900 dark:text-white">
          {{ formattedValue }}
        </span>
        
        <!-- Change Indicator -->
        <div 
          v-if="change !== null && change !== undefined"
          class="flex items-center space-x-1 px-2 py-1 rounded-full text-sm font-medium"
          :class="changeColorClass"
        >
          <i :class="changeIcon"></i>
          <span>{{ Math.abs(change) }}%</span>
        </div>
      </div>
      
      <!-- Secondary Value -->
      <p v-if="secondaryValue" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        {{ secondaryValue }}
      </p>
    </div>

    <!-- Mini Chart -->
    <div v-if="chartData && chartData.length > 0" class="mb-4">
      <IndustryChart
        :type="chartType"
        :series="chartSeries"
        :height="80"
        :show-header="false"
        :show-legend="false"
        :color-scheme="colorScheme"
        :currency="currency"
        :custom-options="miniChartOptions"
      />
    </div>

    <!-- Progress Bar -->
    <div v-if="showProgress" class="mb-4">
      <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
        <span>{{ progressLabel || 'Progress' }}</span>
        <span>{{ progress }}%</span>
      </div>
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
        <div 
          class="h-2 rounded-full transition-all duration-500 ease-in-out"
          :class="progressBarColor"
          :style="{ width: `${Math.min(progress, 100)}%` }"
        ></div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div v-if="metrics && metrics.length > 0" class="grid grid-cols-2 gap-4">
      <div 
        v-for="(metric, index) in metrics" 
        :key="index" 
        class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
      >
        <p class="text-sm font-medium text-gray-900 dark:text-white">
          {{ formatMetricValue(metric.value, metric.type) }}
        </p>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
          {{ metric.label }}
        </p>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="footerText" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
      <p class="text-xs text-gray-600 dark:text-gray-400">
        {{ footerText }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import IndustryChart from './IndustryChart.vue'

const props = defineProps({
  // Basic info
  title: {
    type: String,
    required: true
  },
  subtitle: String,
  value: {
    type: [String, Number],
    required: true
  },
  secondaryValue: String,
  
  // Formatting
  type: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percentage'].includes(value)
  },
  
  // Icon and styling
  icon: {
    type: String,
    default: 'ri-bar-chart-line'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  
  // Change indicator
  change: {
    type: Number,
    default: null
  },
  
  // Chart
  chartData: {
    type: Array,
    default: () => []
  },
  chartType: {
    type: String,
    default: 'line'
  },
  colorScheme: {
    type: String,
    default: 'primary'
  },
  currency: Boolean,
  
  // Progress bar
  showProgress: Boolean,
  progress: {
    type: Number,
    default: 0
  },
  progressLabel: String,
  
  // Metrics
  metrics: {
    type: Array,
    default: () => []
  },
  
  // Footer
  footerText: String,
  
  // Actions
  showAction: Boolean
})

const emit = defineEmits(['action'])

// Computed properties
const formattedValue = computed(() => {
  const value = props.value
  switch (props.type) {
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
})

const iconBgColor = computed(() => {
  const colors = {
    primary: 'bg-blue-500',
    success: 'bg-green-500',
    warning: 'bg-yellow-500',
    danger: 'bg-red-500',
    info: 'bg-indigo-500'
  }
  return colors[props.variant] || colors.primary
})

const changeColorClass = computed(() => {
  if (props.change === null || props.change === undefined) return ''
  return props.change >= 0
    ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
    : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
})

const changeIcon = computed(() => {
  if (props.change === null || props.change === undefined) return ''
  return props.change >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line'
})

const progressBarColor = computed(() => {
  if (props.progress >= 80) return 'bg-green-500'
  if (props.progress >= 60) return 'bg-yellow-500'
  if (props.progress >= 40) return 'bg-orange-500'
  return 'bg-red-500'
})

const chartSeries = computed(() => {
  if (!props.chartData || props.chartData.length === 0) return []
  
  return [{
    name: 'Value',
    data: props.chartData
  }]
})

const miniChartOptions = computed(() => ({
  chart: {
    sparkline: {
      enabled: true
    }
  },
  stroke: {
    width: 3
  },
  fill: {
    opacity: 0.3
  },
  tooltip: {
    enabled: false
  },
  grid: {
    show: false
  },
  xaxis: {
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    labels: { show: false }
  }
}))

const formatMetricValue = (value, type) => {
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
</script>

<style scoped>
.dashboard-stats-card {
  transition: all 0.2s ease-in-out;
}

.dashboard-stats-card:hover {
  transform: translateY(-1px);
}
</style> 