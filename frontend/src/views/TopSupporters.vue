<template>
    <div class="min-h-screen bg-background-light dark:bg-background-dark">
        <!-- Modern Header -->
        <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20 py-6">
                    <!-- Left Side: Navigation and Title -->
                    <div class="flex items-center gap-4">
                        <router-link to="/dashboard"
                            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md">
                            <i class="ri-arrow-left-line text-lg"></i>
                        </router-link>
                        
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                                {{ t('top_supporters') }}
                            </h1>
                            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                                {{ t('top_supporters_description') }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Right Side: Quick Stats -->
                    <div class="hidden md:flex items-center gap-6">
                        <div class="text-right">
                            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Supporters</div>
                            <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ supportersStore.topSupporters.length }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Amount</div>
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">${{ formatCurrency(totalAmount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Date Range Selector - Only show when not on "All Time" -->
            <div v-if="selectedPeriod !== 'all'" class="mb-6">
                <button @click="showDatePicker = true"
                    class="inline-flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-105 shadow-sm">
                    <i class="ri-calendar-line"></i>
                    <span>{{ t('from') }} {{ formatDisplayDate(dateRange.start_date) }} — {{ t('to') }} {{ formatDisplayDate(dateRange.end_date) }}</span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="supportersStore.loading" class="flex justify-center items-center py-16">
                <div class="flex flex-col items-center gap-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
                    <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading supporters...</p>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="supportersStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                        <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Supporters</h3>
                        <p class="text-red-600 dark:text-red-400">{{ supportersStore.error }}</p>
                    </div>
                </div>
            </div>

            <template v-else>
                <!-- Time Period Selector -->
                <div class="mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-2">
                        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                            <button v-for="period in timePeriods" :key="period.value"
                                @click="changeTimePeriod(period.value)" 
                                :class="selectedPeriod === period.value
                                    ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                                    : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700'"
                                class="px-6 py-3 whitespace-nowrap rounded-xl font-medium transition-all duration-200 hover:scale-105">
                                {{ period.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Top Supporters List -->
                <div v-if="supportersStore.topSupporters.length === 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ri-heart-line text-3xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ t('no_supporters_found') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400">Start creating content to see your top supporters here</p>
                </div>

                <div v-else class="space-y-6">
                    <!-- Supporter Cards -->
                    <div v-for="(supporter, index) in supportersStore.topSupporters" :key="supporter.id"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                        <!-- Supporter Header (Always Visible) -->
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-full overflow-hidden ring-4 ring-gray-100 dark:ring-gray-700">
                                        <img :src="supporter.avatar || '/placeholder.svg?height=64&width=64'"
                                            :alt="supporter.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-if="index < 3"
                                        class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full flex items-center justify-center shadow-lg"
                                        :class="getMedalClass(index)">
                                        <i class="ri-award-fill text-white text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ supporter.name }}</p>
                                        <div v-if="index < 3" class="flex items-center gap-1">
                                            <i class="ri-medal-line text-yellow-500"></i>
                                            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">{{ getRankLabel(index) }}</span>
                                        </div>
                                    </div>
                                    <p v-if="supporter.username"
                                        class="text-sm text-gray-500 dark:text-gray-400">
                                        @{{ supporter.username }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(supporter.amount) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Support</p>
                                </div>
                                <button @click="toggleSupporterDetails(supporter.id)" 
                                    class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                                    <i :class="expandedSupporters.includes(supporter.id) ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"
                                        class="text-xl text-gray-600 dark:text-gray-400"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Details -->
                        <div v-if="expandedSupporters.includes(supporter.id)"
                            class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <!-- Filter Controls -->
                            <div class="px-6 py-4 flex gap-3">
                                <button @click="toggleCombine(supporter.id)"
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-105">
                                    <i class="ri-bar-chart-grouped-line"></i>
                                    <span>{{ t('combine') }}</span>
                                </button>
                                <button v-if="selectedPeriod !== 'all'" @click="showDatePicker = true"
                                    class="flex-1 flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-105">
                                    <i class="ri-calendar-line"></i>
                                    <span>{{ t('from') }} {{ formatDisplayDate(dateRange.start_date) }} — {{ t('to') }} {{ formatDisplayDate(dateRange.end_date) }}</span>
                                </button>
                                <div v-else
                                    class="flex-1 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-xl py-2">
                                    {{ t('showing_all_time_data') }}
                                </div>
                            </div>

                            <!-- Chart -->
                            <div class="px-6 pb-4 h-48">
                                <div v-if="isLoading(supporter.id)" class="flex justify-center items-center h-full">
                                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
                                </div>
                                <div v-else-if="!hasChartData(supporter)"
                                    class="flex justify-center items-center h-full text-gray-500 dark:text-gray-400">
                                    <div class="text-center">
                                        <i class="ri-bar-chart-line text-3xl mb-2"></i>
                                        <p>{{ t('no_chart_data') }}</p>
                                    </div>
                                </div>
                                <canvas v-else :id="`chart-${supporter.id}`" class="w-full h-full"></canvas>
                            </div>

                            <!-- Stats Summary -->
                            <div class="px-6 pb-6 space-y-4">
                                <div v-for="(stat, statIndex) in getSupporterStats(supporter)" :key="statIndex"
                                    class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full"
                                            :style="{ backgroundColor: getStatColor(statIndex) }"></div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ stat.name }}</span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">${{ formatCurrency(stat.value) }}</span>
                                </div>

                                <!-- Income Summary -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400 mb-2">
                                        <span>{{ t('gross_income') }}</span>
                                        <span>${{ formatCurrency(supporter.gross_income || supporter.amount) }}</span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                                        <span>{{ t('net_income') }}</span>
                                        <span>${{ formatCurrency(calculateNetIncome(supporter)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More Button -->
                <div v-if="supportersStore.hasMoreSupporters" class="mt-8 text-center">
                    <button @click="loadMoreSupporters" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-xl font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="supportersStore.loadingMore">
                        <i v-if="supportersStore.loadingMore" class="ri-loader-4-line animate-spin"></i>
                        <span>{{ supportersStore.loadingMore ? t('loading') : t('load_more') }}</span>
                    </button>
                </div>
            </template>
        </div>

        <!-- Date Picker Modal -->
        <div v-if="showDatePicker" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('select_date_range') }}</h2>
                    <button @click="showDatePicker = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ t('start_date') }}</label>
                        <input v-model="tempDateRange.start_date" type="date"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-transparent focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ t('end_date') }}</label>
                        <input v-model="tempDateRange.end_date" type="date"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl bg-transparent focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="showDatePicker = false"
                            class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                            {{ t('cancel') }}
                        </button>
                        <button @click="applyDateRange"
                            class="px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-xl font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200">
                            {{ t('apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'
import Chart from 'chart.js/auto'

const { t } = useI18n()

// Dynamic imports for stores
let supportersStore = null
let authStore = null

// Initialize stores and set current user ID
const initializeStores = async () => {
  const [{ useSupportersStore }, { useAuthStore }] = await Promise.all([
    import('@/stores/supportersStore'),
    import('@/stores/authStore')
  ])
  
  supportersStore = useSupportersStore()
  authStore = useAuthStore()
  
  // Set current user ID in the supporters store
  if (authStore.user && authStore.user.id) {
    supportersStore.setCurrentUserId(authStore.user.id)
  }
}

// State
const selectedPeriod = ref('all')
const expandedSupporters = ref([])
const combinedCharts = ref([])
const chartRegistry = ref({}) // Track chart instances
const chartInitializing = ref({}) // Track charts being initialized
const loadingDetails = ref({}) // Track which supporters are loading details
const showDatePicker = ref(false)

// Computed properties for store data
const topSupporters = computed(() => supportersStore?.topSupporters || [])
const loading = computed(() => supportersStore?.loading || false)
const error = computed(() => supportersStore?.error || null)
const loadingMore = computed(() => supportersStore?.loadingMore || false)
const hasMoreSupporters = computed(() => supportersStore?.hasMoreSupporters || false)

// Date range state
const dateRange = ref({
    start_date: '',
    end_date: ''
})

const tempDateRange = ref({
    start_date: '',
    end_date: ''
})

// Format date as YYYY-MM-DD for API
const formatApiDate = (date) => {
    if (!date) return ''
    if (typeof date === 'string' && date.includes('-')) return date

    const d = new Date(date)
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

// Format date as Apr 1, 2025 for display
const formatDisplayDate = (dateString) => {
    if (!dateString) return ''

    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

// Set date range based on selected period
const setDateRangeForPeriod = (period) => {
    const today = new Date()

    if (period === 'all') {
        // For "All Time", explicitly set empty date range
        dateRange.value = {
            start_date: '',
            end_date: ''
        }
    } else if (period === 'year') {
        // This year
        const firstDayOfYear = new Date(today.getFullYear(), 0, 1)
        dateRange.value = {
            start_date: formatApiDate(firstDayOfYear),
            end_date: formatApiDate(today)
        }
    } else if (period === 'month') {
        // This month
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
        dateRange.value = {
            start_date: formatApiDate(firstDayOfMonth),
            end_date: formatApiDate(today)
        }
    } else if (period === 'week') {
        // This week (starting from Sunday)
        const firstDayOfWeek = new Date(today)
        const day = today.getDay() // 0 = Sunday, 1 = Monday, etc.
        firstDayOfWeek.setDate(today.getDate() - day) // Go back to Sunday

        dateRange.value = {
            start_date: formatApiDate(firstDayOfWeek),
            end_date: formatApiDate(today)
        }
    }

    // Update temp date range for the picker
    tempDateRange.value = { ...dateRange.value }
}

const timePeriods = [
    { label: 'All Time', value: 'all' },
    { label: 'This Year', value: 'year' },
    { label: 'This Month', value: 'month' },
    { label: 'This Week', value: 'week' },
]

// Computed properties
const isDateRangeValid = computed(() => {
    return tempDateRange.value.start_date &&
        tempDateRange.value.end_date &&
        new Date(tempDateRange.value.start_date) <= new Date(tempDateRange.value.end_date)
})

// Helper functions
const isLoading = (supporterId) => {
    return loadingDetails.value[supporterId] === true
}

const hasChartData = (supporter) => {
    return supporter && supporter.daily_data && supporter.daily_data.length > 0
}

// Methods
const changeTimePeriod = async (period) => {
    selectedPeriod.value = period

    // Update date range based on selected period
    setDateRangeForPeriod(period)

    // Clear all expanded supporters and charts
    expandedSupporters.value = []
    Object.keys(chartRegistry.value).forEach(supporterId => {
        safeDestroyChart(parseInt(supporterId))
    })

    // Fetch data with updated parameters
    const params = { period }

    // Only add date parameters if not "all" period
    if (period !== 'all') {
        if (dateRange.value.start_date) params.start_date = dateRange.value.start_date
        if (dateRange.value.end_date) params.end_date = dateRange.value.end_date
    }

    if (!supportersStore) {
        await initializeStores()
    }
    supportersStore.fetchTopSupporters(params)
}

const loadMoreSupporters = async () => {
    if (!supportersStore) {
        await initializeStores()
    }
    
    const params = { period: selectedPeriod.value }

    // Only add date parameters if not "all" period
    if (selectedPeriod.value !== 'all') {
        if (dateRange.value.start_date) params.start_date = dateRange.value.start_date
        if (dateRange.value.end_date) params.end_date = dateRange.value.end_date
    }

    supportersStore.loadMoreSupporters(params)
}

const formatCurrency = (value) => {
    return parseFloat(value || 0).toFixed(2)
}

const getMedalClass = (index) => {
    switch (index) {
        case 0: return 'bg-yellow-500'  // Gold
        case 1: return 'bg-gray-400'    // Silver
        case 2: return 'bg-amber-700'   // Bronze
        default: return 'bg-gray-300'
    }
}

// Updated color function to match the screenshot
const getStatColor = (index) => {
    // Match the colors from the screenshot:
    // Purple for Tips, Amber for Subscriptions, Blue for Media
    const colors = ['#9333ea', '#d97706', '#3b82f6']
    return colors[index % colors.length]
}

// Calculate net income as the sum of all income sources
const calculateNetIncome = (supporter) => {
    if (supporter.net_income) return supporter.net_income

    const stats = getSupporterStats(supporter)
    let total = 0
    stats.forEach(stat => {
        total += parseFloat(stat.value || 0)
    })

    return total
}

// Safely destroy a chart instance
const safeDestroyChart = (supporterId) => {
    if (chartRegistry.value[supporterId]) {
        try {
            chartRegistry.value[supporterId].destroy()
        } catch (e) {
            console.error(`Error destroying chart for supporter ${supporterId}:`, e)
        }
        chartRegistry.value[supporterId] = null
    }
}

// Toggle supporter details
const toggleSupporterDetails = async (supporterId) => {
    if (!supportersStore) {
        await initializeStores()
    }
    
    const index = expandedSupporters.value.indexOf(supporterId)
    if (index === -1) {
        // Expand the supporter card
        expandedSupporters.value.push(supporterId)

        // Fetch detailed data for this supporter if not already loaded
        const supporter = supportersStore.topSupporters.find(s => s.id === supporterId)
        if (supporter && !supporter.daily_data) {
            loadingDetails.value[supporterId] = true

            const params = {}
            // Only add date parameters if not "all" period
            if (selectedPeriod.value !== 'all') {
                if (dateRange.value.start_date) params.start_date = dateRange.value.start_date
                if (dateRange.value.end_date) params.end_date = dateRange.value.end_date
            }

            supportersStore.getSupporterDetails(supporterId, params)
                .then(() => {
                    // Data loaded successfully
                    loadingDetails.value[supporterId] = false

                    // Initialize chart after data is loaded
                    nextTick(() => {
                        initChart(supporterId)
                    })
                })
                .catch(error => {
                    console.error(`Error loading details for supporter ${supporterId}:`, error)
                    loadingDetails.value[supporterId] = false
                })
        } else {
            // Data already loaded, initialize chart
            nextTick(() => {
                initChart(supporterId)
            })
        }
    } else {
        // Collapse the supporter card
        expandedSupporters.value.splice(index, 1)
        // Destroy chart instance
        safeDestroyChart(supporterId)
    }
}

const toggleCombine = (supporterId) => {
    const index = combinedCharts.value.indexOf(supporterId)
    if (index === -1) {
        combinedCharts.value.push(supporterId)
    } else {
        combinedCharts.value.splice(index, 1)
    }

    // Destroy existing chart and recreate it
    safeDestroyChart(supporterId)
    nextTick(() => {
        initChart(supporterId)
    })
}

const getSupporterStats = (supporter) => {
    // Use the stats from the API if available
    if (supporter.stats) return supporter.stats

    // Otherwise, create stats from individual fields
    return [
        { name: 'Tips', value: supporter.tips || 0 },
        { name: 'Subscriptions', value: supporter.subscriptions || 0 },
        { name: 'Media', value: supporter.media || 0 }
    ]
}

// Initialize chart for a supporter
const initChart = (supporterId) => {
    // Skip if chart is already being initialized
    if (chartInitializing.value[supporterId]) {
        return
    }

    // Mark this chart as initializing
    chartInitializing.value[supporterId] = true

    // Get the supporter data
    const supporter = supportersStore.topSupporters.find(s => s.id === supporterId)
    if (!supporter || !supporter.daily_data || supporter.daily_data.length === 0) {
        chartInitializing.value[supporterId] = false
        return
    }

    // Get the canvas element
    const chartElement = document.getElementById(`chart-${supporterId}`)
    if (!chartElement) {
        console.warn(`Canvas element for supporter ${supporterId} not found`)
        chartInitializing.value[supporterId] = false
        return
    }

    // Destroy existing chart if it exists
    safeDestroyChart(supporterId)

    try {
        // Prepare chart data
        const isCombined = combinedCharts.value.includes(supporterId)
        const dailyData = supporter.daily_data
        const labels = dailyData.map(day => day.date)

        // Prepare datasets
        const datasets = []

        if (isCombined) {
            // Combined data
            datasets.push({
                label: 'Total',
                data: dailyData.map(day => {
                    const tips = parseFloat(day.tips || 0)
                    const subscriptions = parseFloat(day.subscriptions || 0)
                    const media = parseFloat(day.media || 0)
                    return tips + subscriptions + media
                }),
                borderColor: '#3b82f6', // Blue
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                pointRadius: 2,
                borderWidth: 3,
                fill: true
            })
        } else {
            // Individual lines for each category - UPDATED COLORS TO MATCH STATS
            datasets.push({
                label: 'Tips',
                data: dailyData.map(day => parseFloat(day.tips || 0)),
                borderColor: '#9333ea', // Purple
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                tension: 0.4,
                pointRadius: 2,
                borderWidth: 3
            })

            datasets.push({
                label: 'Subscriptions',
                data: dailyData.map(day => parseFloat(day.subscriptions || 0)),
                borderColor: '#d97706', // Amber
                backgroundColor: 'rgba(217, 119, 6, 0.1)',
                tension: 0.4,
                pointRadius: 2,
                borderWidth: 3
            })

            datasets.push({
                label: 'Media',
                data: dailyData.map(day => parseFloat(day.media || 0)),
                borderColor: '#3b82f6', // Blue (changed from green to match the stats)
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                pointRadius: 2,
                borderWidth: 3
            })
        }

        // Create chart
        chartRegistry.value[supporterId] = new Chart(chartElement, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '$' + value.toFixed(2)
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 7
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': $' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        })

        console.log(`Chart initialized for supporter ${supporterId}`)
    } catch (error) {
        console.error(`Error initializing chart for supporter ${supporterId}:`, error)
    } finally {
        // Mark initialization as complete
        chartInitializing.value[supporterId] = false
    }
}

const applyDateFilter = async () => {
    if (!isDateRangeValid.value) return

    if (!supportersStore) {
        await initializeStores()
    }

    dateRange.value = {
        start_date: tempDateRange.value.start_date,
        end_date: tempDateRange.value.end_date
    }

    showDatePicker.value = false

    // Clear all expanded supporters and charts
    expandedSupporters.value = []
    Object.keys(chartRegistry.value).forEach(supporterId => {
        safeDestroyChart(parseInt(supporterId))
    })

    // Fetch data with updated parameters
    supportersStore.fetchTopSupporters({
        period: selectedPeriod.value,
        start_date: dateRange.value.start_date,
        end_date: dateRange.value.end_date
    })
}

// Lifecycle hooks
onMounted(async () => {
    // Initialize stores first
    await initializeStores()
    
    // Start with "All Time" selected and no date constraints
    selectedPeriod.value = 'all'
    setDateRangeForPeriod('all')

    // Fetch all time data without date constraints
    supportersStore.fetchTopSupporters({ period: 'all' })
})

// Watch for changes in supporter data to update charts
watch(() => topSupporters.value, (newSupporters) => {
    // For each expanded supporter, check if we need to initialize or update the chart
    expandedSupporters.value.forEach(supporterId => {
        const supporter = newSupporters.find(s => s.id === supporterId)
        if (supporter && supporter.daily_data && !chartRegistry.value[supporterId]) {
            nextTick(() => {
                initChart(supporterId)
            })
        }
    })
}, { deep: true })

// Clean up all charts when component is unmounted
onBeforeUnmount(() => {
    Object.keys(chartRegistry.value).forEach(supporterId => {
        safeDestroyChart(parseInt(supporterId))
    })
})
</script>