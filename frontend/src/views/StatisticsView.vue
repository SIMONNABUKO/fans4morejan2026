  <template>
    <div class="min-h-screen bg-background-light dark:bg-background-dark w-full max-w-full overflow-x-hidden">
      <!-- Modern Header -->
      <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-20 py-6">
            <!-- Left Side: Navigation and Title -->
            <div class="flex items-center gap-4">
              <button @click="goBack" class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md">
                <i class="ri-arrow-left-line text-lg"></i>
              </button>
              
              <div class="flex flex-col">
                <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                  Profile Statistics
                </h1>
                <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                  Track your performance and earnings analytics
                </p>
              </div>
            </div>
            
            <!-- Right Side: Quick Stats -->
            <div class="hidden md:flex items-center gap-6">
              <div class="text-right">
                <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Revenue</div>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">${{ formatCurrency(totalStats.gross) }}</div>
              </div>
              <div class="text-right">
                <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Net Income</div>
                <div class="text-lg font-bold text-primary-light dark:text-primary-dark">${{ formatCurrency(totalStats.net) }}</div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Tab Navigation -->
      <div class="sticky top-20 z-10 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-2">
            <div class="flex gap-2">
              <button v-for="tab in tabs" :key="tab.id"
                      @click="activeTab = tab.id"
                      :class="activeTab === tab.id 
                        ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                        : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700'"
                      class="flex-1 py-3 px-4 rounded-xl font-medium transition-all duration-200 text-center text-sm">
                {{ tab.label }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Tab Content -->
        <div v-if="activeTab === 'overview'" class="space-y-8">
          <!-- Period Selector -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ selectedPeriod?.label || '-' }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  {{ selectedPeriod?.dateRange || '-' }}
                </p>
              </div>
              <button @click="showPeriodSelector = !showPeriodSelector" 
                      class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                <i class="ri-arrow-down-s-line text-xl"></i>
              </button>
            </div>

            <!-- Period Selector Dropdown -->
            <div v-if="showPeriodSelector" class="mt-4 space-y-2">
              <button v-for="period in timePeriods" :key="period.id"
                      @click="selectPeriod(period)"
                      class="w-full text-left p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-[1.02]">
                <div class="font-semibold text-gray-900 dark:text-white">{{ period.label }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ period.dateRange }}</div>
              </button>
            </div>
          </div>

          <!-- Revenue Chart Container -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <IndustryChart
              type="area"
              :series="lineChartSeries"
              :height="550"
              width="100%"
              :currency="true"
              :gradient="true"
              title="Revenue Overview"
              subtitle="Track your earnings across different revenue streams"
              color-scheme="revenue"
              :show-summary="true"
              :summary-data="revenueSummaryData"
              :loading="statisticsStore.loading"
              :custom-options="revenueChartOptions"
              empty-title="No revenue data"
              empty-message="Revenue data will appear here when you start earning"
            />
          </div>

          <!-- Total Revenue Summary -->
          <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Total Revenue</p>
                <p class="text-3xl font-bold">${{ formatCurrency(totalStats.gross) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm opacity-90">Net Income</p>
                <p class="text-2xl font-semibold">${{ formatCurrency(totalStats.net) }}</p>
              </div>
            </div>
          </div>

          <!-- Earnings Breakdown -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Earnings Bar Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <IndustryChart
                type="bar"
                :series="earningsChartSeries"
                :height="350"
                :currency="true"
                title="Revenue Breakdown"
                subtitle="Compare gross vs net earnings by source"
                color-scheme="revenue"
                :show-data-labels="true"
                :categories="earningsCategories"
                :loading="statisticsStore.loading"
                empty-title="No earnings data"
                empty-message="Earnings breakdown will appear here when available"
              />
            </div>

            <!-- Earnings Donut Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <IndustryChart
                type="donut"
                :series="earningsDonutSeries"
                :height="350"
                :currency="true"
                title="Revenue Distribution"
                subtitle="Percentage breakdown of total revenue"
                color-scheme="revenue"
                :custom-options="earningsDonutOptions"
                :loading="statisticsStore.loading"
                empty-title="No revenue data"
                empty-message="Revenue distribution will appear here when available"
              />
            </div>
          </div>

          <!-- Earnings Table -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detailed Earnings Breakdown</h3>
              <div class="flex justify-between mt-3 text-sm text-gray-500 dark:text-gray-400">
                <span>Source</span>
                <div class="flex space-x-8">
                  <span>Gross</span>
                  <span>Net</span>
                </div>
              </div>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
              <div v-for="item in earningsData" :key="item.id" 
                   class="p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                <div class="flex items-center space-x-4">
                  <div :class="getSourceColor(item.id)"
                       class="w-4 h-4 rounded-full"></div>
                  <span class="capitalize font-medium text-gray-900 dark:text-white">{{ item.name }}</span>
                </div>
                <div class="flex space-x-8 text-right">
                  <span class="w-24 font-semibold text-gray-900 dark:text-white">
                    ${{ formatCurrency(item.gross) }}
                  </span>
                  <span class="w-24 font-semibold text-gray-900 dark:text-white">
                    ${{ formatCurrency(item.net) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Monthly Summary -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-calendar-line text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ monthlySummary?.month || '-' }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Summary</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">${{ formatCurrency(monthlySummary?.net || 0) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Net Earnings</p>
              </div>
            </div>
          </div>

          <!-- Quick Stats Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-hand-coin-line text-yellow-600 dark:text-yellow-400 text-lg"></i>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Top Source</span>
              </div>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ topSource?.name || '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">${{ formatCurrency(topSource?.gross || 0) }}</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-percent-line text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Conversion</span>
              </div>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ conversionRate ? conversionRate + '%' : '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Gross to Net</p>
            </div>
          </div>
        </div>
  
      <!-- Analytics Tab Content -->
      <div v-if="activeTab === 'analytics'" class="space-y-8">
        <!-- Content Type Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content Type</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button v-for="contentType in contentTypes" :key="contentType.id"
                    @click="analyticsStore.setFilter('contentType', contentType.id)"
                    :class="analyticsFilters.contentType === contentType.id 
                      ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                    class="flex items-center justify-center space-x-2 py-3 px-4 rounded-xl font-medium transition-all duration-200 hover:scale-105">
              <i :class="contentType.icon" class="text-lg"></i>
              <span>{{ contentType.label }}</span>
            </button>
          </div>
        </div>

        <!-- Analytics Period -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-primary-light dark:text-primary-dark">{{ selectedAnalyticsPeriod.label }}</h3>
            <button @click="showAnalyticsPeriodSelector = !showAnalyticsPeriodSelector" 
                    class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
              <i class="ri-arrow-down-s-line text-xl"></i>
            </button>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ selectedAnalyticsPeriod.dateRange }}
          </p>

          <!-- Analytics Period Selector Dropdown -->
          <div v-if="showAnalyticsPeriodSelector" class="mt-4 space-y-2">
            <button v-for="period in analyticsTimePeriods" :key="period.id"
                    @click="selectAnalyticsPeriod(period)"
                    class="w-full text-left p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-[1.02]">
              <div class="font-semibold text-gray-900 dark:text-white">{{ period.label }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">{{ period.dateRange }}</div>
            </button>
          </div>
        </div>

        <!-- Metrics Filter -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Metrics</h3>
          <div class="flex flex-wrap gap-3">
            <button v-for="metric in analyticsMetrics" :key="metric.id"
                    @click="analyticsStore.setFilter('metric', metric.id)"
                    :class="analyticsFilters.metric === metric.id 
                      ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600'"
                    class="px-4 py-2 rounded-full font-medium transition-all duration-200 hover:scale-105">
              {{ metric.label }}
            </button>
          </div>
        </div>

        <!-- Analytics Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatMetric(analyticsSummary.total, analyticsFilters.metric) }}</h3>
              <p class="text-gray-500 dark:text-gray-400">{{ getCurrentContentTypeLabel() }} - {{ getCurrentMetricLabel() }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <i class="ri-arrow-up-line text-green-600 dark:text-green-400"></i>
              <span class="text-green-600 dark:text-green-400 font-semibold">{{ analyticsSummary.growth }}</span>
            </div>
          </div>

          <!-- Analytics Chart -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 mb-4">
            <IndustryChart
              type="area"
              :series="analyticsChartSeries"
              :height="350"
              :currency="['purchases', 'tips'].includes(analyticsFilters.metric)"
              :gradient="true"
              :title="`${getCurrentMetricLabel()} Analytics`"
              :subtitle="`${getCurrentContentTypeLabel()} performance over time`"
              color-scheme="engagement"
              :loading="analyticsLoading"
              :empty-title="`No ${getCurrentMetricLabel().toLowerCase()} data`"
              :empty-message="`${getCurrentMetricLabel()} data will appear here when available`"
            />
          </div>
        </div>

        <!-- Analytics Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detailed Analytics</h3>
          </div>
          
          <!-- Table Header -->
          <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
            <div class="grid grid-cols-3 gap-4 text-sm font-semibold text-gray-600 dark:text-gray-400">
              <span>Date</span>
              <span v-if="analyticsFilters.contentType === 'posts'">Views / Likes / Comments</span>
              <span v-else>Sender</span>
              <span>Price / Tips / Purchases</span>
            </div>
          </div>
          
          <!-- Table Data -->
          <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-64 overflow-y-auto">
            <div v-if="analyticsLoading" class="p-6 text-center text-gray-500 dark:text-gray-400">Loading...</div>
            <div v-else-if="!analyticsTableData.length" class="p-6 text-center text-gray-500 dark:text-gray-400">No data available for this period.</div>
            <div v-else v-for="row in analyticsTableData" :key="row.id"
                 class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ row.date }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ row.time }}</p>
                </div>
                <div v-if="analyticsFilters.contentType === 'posts'">
                  <p class="font-semibold text-gray-900 dark:text-white">{{ formatNumber(row.viewers) }} / {{ formatNumber(row.likes) }} / {{ formatNumber(row.comments) }}</p>
                </div>
                <div v-else>
                    <p class="font-semibold text-gray-900 dark:text-white">-</p> 
                </div>
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">${{ formatCurrency(row.price) }} / ${{ formatCurrency(row.tips) }} / ${{ formatCurrency(row.purchases) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

        <!-- Reach Tab Content -->
        <div v-if="activeTab === 'reach'" class="space-y-8">
          <!-- Reach Period Selector -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-primary-light dark:text-primary-dark">{{ reachSelectedPeriod.label }}</h3>
              <button @click="showReachPeriodSelector = !showReachPeriodSelector" 
                      class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                <i class="ri-arrow-down-s-line text-xl"></i>
              </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ reachSelectedPeriod.dateRange }}
            </p>

            <!-- Reach Period Selector Dropdown -->
            <div v-if="showReachPeriodSelector" class="mt-4 space-y-2">
              <button v-for="period in reachTimePeriods" :key="period.id"
                      @click="selectReachPeriod(period)"
                      class="w-full text-left p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-[1.02]">
                <div class="font-semibold text-gray-900 dark:text-white">{{ period.label }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ period.dateRange }}</div>
              </button>
            </div>
          </div>

          <!-- Profile Visitors -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center space-x-4 mb-6">
              <div class="w-12 h-12 bg-primary-light dark:bg-primary-dark rounded-xl flex items-center justify-center">
                <i class="ri-user-line text-white text-xl"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Profile Visitors</h3>
            </div>
            
            <!-- Add your reach/visitors content here -->
            <div class="text-center py-12">
              <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="ri-user-line text-3xl text-gray-400 dark:text-gray-500"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Reach Analytics</h3>
              <p class="text-gray-500 dark:text-gray-400">Profile visitor data will appear here</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useStatisticsStore } from '@/stores/statisticsStore'
import { useAnalyticsStore } from '@/stores/analyticsStore'
import { useReachStore } from '@/stores/reachStore'
import { useTopFansStore } from '@/stores/topFansStore'
import dayjs from 'dayjs'
import VueApexCharts from 'vue3-apexcharts'
import IndustryChart from '@/components/charts/IndustryChart.vue'

// --- STORES ---
const statisticsStore = useStatisticsStore()
const analyticsStore = useAnalyticsStore()
const reachStore = useReachStore()
const topFansStore = useTopFansStore()

// --- UI STATE ---
const activeTab = ref('overview')
const showPeriodSelector = ref(false)
const showAnalyticsPeriodSelector = ref(false)
const showReachPeriodSelector = ref(false)
const showFansPeriodSelector = ref(false)
const selectedFanFilter = ref('all')


// --- STATIC DATA ---
const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'analytics', label: 'Analytics' },
  { id: 'reach', label: 'Reach' },
  { id: 'fans', label: 'Top Fans' }
]

const contentTypes = [
  { id: 'posts', label: 'Posts', icon: 'ri-image-line' },
  { id: 'messages', label: 'Messages', icon: 'ri-chat-3-line' }
]

const analyticsMetrics = [
  { id: 'purchases', label: 'Purchases' },
  { id: 'tips', label: 'Tips' },
  { id: 'views', label: 'Views' },
  { id: 'likes', label: 'Likes' },
  { id: 'comments', label: 'Comments' }
]

const seriesInfo = [
    { name: 'Tips', color: '#a855f7' },
    { name: 'Purchases', color: '#f59e42' },
    { name: 'Subscriptions', color: '#2563eb' },
    { name: 'Referrals', color: '#10b981' }
]


// --- OVERVIEW TAB STATE & LOGIC ---
const earningsData = computed(() => statisticsStore.overview.breakdown)
const monthlySummary = computed(() => statisticsStore.overview.monthly_summary)
const topSource = computed(() => statisticsStore.overview.top_source)
const conversionRate = computed(() => statisticsStore.overview.conversion_rate)
const totalStats = computed(() => statisticsStore.overview.total)

const getPeriodOptions = () => {
  const now = dayjs()
  return [
    { id: 'thisMonth', label: 'This month', start: now.startOf('month'), end: now },
    { id: 'last7', label: 'Last 7 days', start: now.subtract(6, 'day').startOf('day'), end: now },
    { id: 'last30', label: 'Last 30 days', start: now.subtract(29, 'day').startOf('day'), end: now },
    { id: 'last90', label: 'Last 90 days', start: now.subtract(89, 'day').startOf('day'), end: now }
  ].map(period => ({
    ...period,
    dateRange: `${period.start.format('MMM D, YYYY')} - ${period.end.format('MMM D, YYYY')}`
  }))
}
const timePeriods = ref(getPeriodOptions())
const selectedPeriod = ref(timePeriods.value[0] || { label: 'Select period', dateRange: '' })

const selectPeriod = (period) => {
  selectedPeriod.value = period
  showPeriodSelector.value = false
}

const fetchOverview = async () => {
  if (!selectedPeriod.value || !selectedPeriod.value.start) return;
  await statisticsStore.fetchOverviewStatistics({
    start_date: selectedPeriod.value.start.format('YYYY-MM-DD'),
    end_date: selectedPeriod.value.end.format('YYYY-MM-DD')
  })
}

const lineChartSeries = computed(() => [
  { name: 'Tips', data: statisticsStore.overview.chart.map(d => [new Date(d.date).getTime(), d.tips ?? 0]) },
  { name: 'Purchases', data: statisticsStore.overview.chart.map(d => [new Date(d.date).getTime(), d.purchases ?? 0]) },
  { name: 'Subscriptions', data: statisticsStore.overview.chart.map(d => [new Date(d.date).getTime(), d.subscriptions ?? 0]) },
  { name: 'Referrals', data: statisticsStore.overview.chart.map(d => [new Date(d.date).getTime(), d.referrals ?? 0]) },
])

// Revenue summary data for the new chart
const revenueSummaryData = computed(() => [
  { label: 'Total Revenue', value: totalStats.value.gross, type: 'currency' },
  { label: 'Net Income', value: totalStats.value.net, type: 'currency' },
  { label: 'Growth Rate', value: 12.5, type: 'percentage' },
  { label: 'Active Sources', value: earningsData.value.length, type: 'number' }
])

// Fans chart series for mixed chart
const fansChartSeries = computed(() => {
  const chartData = topFansStore.fansChartData || []
  return [
    {
      name: 'Revenue',
      type: 'column',
      data: chartData.map(point => ({ x: new Date(point.date).getTime(), y: point.revenue || 0 }))
    },
    {
      name: 'Subscribers',
      type: 'line',
      data: chartData.map(point => ({ x: new Date(point.date).getTime(), y: point.subscribers || 0 }))
    }
  ]
})

// Mixed chart custom options for fans chart
const mixedChartOptions = computed(() => ({
  yaxis: [
    {
      title: {
        text: 'Revenue ($)',
        style: { color: '#64748b', fontSize: '12px', fontWeight: 600 }
      },
      labels: {
        formatter: (val) => `$${val.toFixed(2)}`
      }
    },
    {
      opposite: true,
      title: {
        text: 'Subscribers',
        style: { color: '#64748b', fontSize: '12px', fontWeight: 600 }
      },
      labels: {
        formatter: (val) => Math.round(val).toString()
      }
    }
  ]
}))

// Countries chart data for reach section
const countriesChartData = computed(() => ({
  series: (reachStore.topCountries || []).map(country => country.total),
  labels: (reachStore.topCountries || []).map(country => country.name)
}))

const countriesChartOptions = computed(() => ({
  labels: (reachStore.topCountries || []).map(country => country.name),
  dataLabels: {
    formatter: (val, opts) => {
      const country = reachStore.topCountries[opts.seriesIndex]
      return `${country?.flag || ''} ${val.toFixed(1)}%`
    }
  }
}))

// Visitor type chart data
const visitorTypeChartData = computed(() => {
  const profileVisitors = reachStore.profileVisitors || []
  return {
    series: profileVisitors.map(visitor => visitor.count),
    labels: profileVisitors.map(visitor => visitor.label)
  }
})

const visitorTypeChartOptions = computed(() => ({
  labels: (reachStore.profileVisitors || []).map(visitor => visitor.label),
  plotOptions: {
    pie: {
      donut: {
        labels: {
          total: {
            label: 'Total Visitors',
            formatter: (w) => {
              const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              return formatCompactNumber(total)
            }
          }
        }
      }
    }
  }
}))

const visitorTypeSummary = computed(() => {
  const visitors = reachStore.profileVisitors || []
  const total = visitors.reduce((sum, v) => sum + v.count, 0)
  return [
    { label: 'Total Visitors', value: total, type: 'number' },
    { label: 'Unique Rate', value: 85.2, type: 'percentage' },
    { label: 'Return Rate', value: 14.8, type: 'percentage' },
    { label: 'Bounce Rate', value: 32.1, type: 'percentage' }
  ]
})

// Custom options for revenue chart to improve spacing and visibility
const revenueChartOptions = computed(() => ({
  chart: {
    width: '100%',
    toolbar: {
      show: true,
      tools: {
        download: true,
        selection: true,
        zoom: true,
        zoomin: true,
        zoomout: true,
        pan: true,
        reset: true
      }
    }
  },
  plotOptions: {
    area: {
      fillTo: 'end'
    }
  },
  xaxis: {
    labels: {
      style: {
        fontSize: '13px',
        fontWeight: 500
      }
    },
    tooltip: {
      enabled: true
    }
  },
  yaxis: {
    labels: {
      style: {
        fontSize: '13px',
        fontWeight: 500
      }
    }
  },
  stroke: {
    width: 3,
    curve: 'smooth'
  },
  markers: {
    size: 5,
    strokeWidth: 2,
    strokeColors: '#ffffff',
    hover: {
      size: 7,
      sizeOffset: 3
    }
  },
  grid: {
    show: true,
    borderColor: '#e5e7eb',
    strokeDashArray: 4,
    padding: {
      left: 10,
      right: 10,
      top: 10,
      bottom: 10
    }
  },
  tooltip: {
    enabled: true,
    shared: true,
    intersect: false,
    style: {
      fontSize: '14px'
    },
    x: {
      format: 'MMM dd, yyyy'
    },
    y: {
      formatter: (val) => `$${val.toFixed(2)}`
    }
  },
  responsive: [
    {
      breakpoint: 768,
      options: {
        chart: {
          height: 400
        },
        xaxis: {
          labels: {
            style: {
              fontSize: '11px'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              fontSize: '11px'
            }
          }
        }
      }
    }
  ]
}))

// Earnings chart data
const earningsCategories = computed(() => (earningsData.value || []).map(item => item.name))

const earningsChartSeries = computed(() => [
  {
    name: 'Gross Earnings',
    data: (earningsData.value || []).map(item => item.gross)
  },
  {
    name: 'Net Earnings', 
    data: (earningsData.value || []).map(item => item.net)
  }
])

const earningsDonutSeries = computed(() => (earningsData.value || []).map(item => item.gross))

const earningsDonutOptions = computed(() => ({
  labels: (earningsData.value || []).map(item => item.name),
  plotOptions: {
    pie: {
      donut: {
        labels: {
          total: {
            label: 'Total Revenue',
            formatter: (w) => {
              const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              return `$${total.toFixed(2)}`
            }
          }
        }
      }
    }
  }
}))

const lineChartOptions = computed(() => ({
  chart: { type: 'line', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
  xaxis: {
    type: 'datetime',
    tickAmount: 6,
    labels: { format: 'MMM dd', rotate: 0, hideOverlappingLabels: true, style: { colors: '#9ca3af', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false }
  },
  yaxis: { labels: { formatter: val => `$${parseFloat(val).toFixed(2)}`, style: { colors: ['#9ca3af'] } } },
  tooltip: { shared: true, intersect: false, x: { format: 'MMM dd, yyyy' }, y: { formatter: val => val !== undefined ? `$${parseFloat(val).toFixed(2)}` : '$0.00' } },
  stroke: { curve: 'smooth', width: 2 },
  markers: { size: 4, hover: { size: 6 } },
  grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
  legend: { show: false },
  colors: seriesInfo.map(s => s.color)
}))


// --- ANALYTICS TAB STATE & LOGIC ---
const analyticsLoading = computed(() => analyticsStore.loading)
const analyticsTableData = computed(() => analyticsStore.tableData)
const analyticsChartData = computed(() => analyticsStore.chartData)
const analyticsSummary = computed(() => analyticsStore.summary)
const analyticsFilters = computed(() => analyticsStore.filters)

const analyticsTimePeriods = ref([
  { id: 'last30', label: 'Last 30 days' },
  { id: 'last90', label: 'Last 90 days' },
  { id: 'last180', label: 'Last 180 days' }
].map(p => ({ ...p, dateRange: `${dayjs().subtract(parseInt(p.id.replace('last', '')), 'day').format('MMM D, YYYY')} - ${dayjs().format('MMM D, YYYY')}` })))

const selectedAnalyticsPeriod = ref(analyticsTimePeriods.value[0])

const selectAnalyticsPeriod = async (period) => {
  selectedAnalyticsPeriod.value = period
  await analyticsStore.setFilter('period', period.id)
  showAnalyticsPeriodSelector.value = false
}

const getCurrentContentTypeLabel = () => {
  const contentType = contentTypes.find(ct => ct.id === analyticsFilters.value.contentType)
  return contentType ? contentType.label : ''
}

const getCurrentMetricLabel = () => {
  const metric = analyticsMetrics.find(m => m.id === analyticsFilters.value.metric)
  return metric ? metric.label : ''
}

const analyticsChartOptions = computed(() => ({
  chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  markers: { size: 0 },
  xaxis: {
    type: 'datetime',
    tickAmount: 6,
    labels: { format: 'MMM dd', rotate: 0, style: { colors: '#9ca3af' } },
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: { 
    labels: { 
      formatter: (val) => {
        const isCurrency = ['purchases', 'tips'].includes(analyticsFilters.value.metric)
        return isCurrency ? `$${parseFloat(val || 0).toFixed(2)}` : formatNumber(val || 0)
      }, 
      style: { colors: ['#9ca3af'] } 
    } 
  },
  tooltip: { 
    x: { format: 'MMM dd, yyyy' }, 
    y: { 
      formatter: (val) => {
        const isCurrency = ['purchases', 'tips'].includes(analyticsFilters.value.metric)
        return isCurrency ? `$${parseFloat(val || 0).toFixed(2)}` : formatNumber(val || 0)
      }
    } 
  },
  grid: { borderColor: '#e5e7eb', strokeDashArray: 4 },
  legend: { show: false },
  colors: ['#a855f7']
}))

const analyticsChartSeries = computed(() => [{
  name: getCurrentMetricLabel(),
  data: analyticsChartData.value.map(d => {
    // Convert date string to timestamp for ApexCharts
    const date = new Date(d.date).getTime()
    return [date, d.total]
  })
}])


// --- REACH & FANS TAB (Static Placeholders) ---
const reachSelectedPeriod = ref({ id: 'last90', label: 'Last 90 days', dateRange: 'Mar 12, 2025 - Jun 10, 2025 (local time UTC -04:00)' })
const fansSelectedPeriod = ref({ id: 'last90', label: 'Last 90 days', dateRange: 'Mar 12, 2025 - Jun 10, 2025 (local time UTC -04:00)' })

// --- REACH & FANS DATA FETCHING ---
const fetchReachData = async () => {
  if (!reachSelectedPeriod.value || !reachSelectedPeriod.value.id) return;
  
  try {
    await reachStore.fetchReachStatistics({
      period: reachSelectedPeriod.value.id,
      start_date: reachSelectedPeriod.value.start_date,
      end_date: reachSelectedPeriod.value.end_date
    })
  } catch (error) {
    console.error('Error fetching reach data:', error)
  }
}

const fetchTopFansData = async () => {
  if (!fansSelectedPeriod.value || !fansSelectedPeriod.value.id) return;
  
  try {
    await topFansStore.fetchTopFansStatistics({
      period: fansSelectedPeriod.value.id,
      filter: selectedFanFilter.value,
      start_date: fansSelectedPeriod.value.start_date,
      end_date: fansSelectedPeriod.value.end_date
    })
  } catch (error) {
    console.error('Error fetching top fans data:', error)
  }
}

// Update period selection functions to fetch data
const selectReachPeriod = async (period) => { 
  reachSelectedPeriod.value = period; 
  showReachPeriodSelector.value = false;
  await fetchReachData();
}

const selectFansPeriod = async (period) => { 
  fansSelectedPeriod.value = period; 
  showFansPeriodSelector.value = false;
  await fetchTopFansData();
}

const reachTimePeriods = [
    { id: 'last30', label: 'Last 30 days', dateRange: 'May 11, 2025 - Jun 10, 2025 (local time UTC -04:00)'},
    { id: 'last90', label: 'Last 90 days', dateRange: 'Mar 12, 2025 - Jun 10, 2025 (local time UTC -04:00)'},
    { id: 'last180', label: 'Last 6 months', dateRange: 'Dec 12, 2024 - Jun 10, 2025 (local time UTC -04:00)'}
]
const fanFilters = ref([
    { id: 'all', label: 'All Time' },
    { id: 'newest', label: 'Newest' },
    { id: 'spend', label: 'Total Spend' }
])


// --- UTILITY & FORMATTING ---
const formatCurrency = (amount) => {
  if (typeof amount !== 'number' && typeof amount !== 'string') return '0.00'
  return parseFloat(amount).toFixed(2)
}

const formatNumber = (num) => {
  if (typeof num !== 'number') return 0
  return num.toLocaleString()
}

const formatCompactNumber = (num) => {
  if (typeof num !== 'number') return 0;
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num.toLocaleString()
}

const formatMetric = (value, metric) => {
  if (typeof value !== 'number') value = 0;
  const isCurrency = ['purchases', 'tips'].includes(metric)
  return isCurrency ? `$${formatCurrency(value)}` : formatNumber(value)
}

const getSourceColor = (sourceId) => {
  const colors = {
    subscriptions: 'bg-primary-light dark:bg-primary-dark',
    tips: 'bg-accent-warning',
    purchases: 'bg-accent-success',
    referrals: 'bg-text-light-secondary dark:text-text-dark-secondary'
  }
  return colors[sourceId] || 'bg-text-light-secondary'
}

const goBack = () => { /* Navigation logic */ }


// --- LIFECYCLE HOOKS ---
onMounted(async () => {
  await fetchOverview()
  // Initialize analytics store
  await analyticsStore.initialize()
})

watch(selectedPeriod, fetchOverview, { immediate: false })

// Watch for individual filter changes
watch(() => analyticsFilters.value.contentType, async (newContentType) => {
  if (activeTab.value === 'analytics') {
    console.log('Content type changed to:', newContentType)
    await analyticsStore.fetchAnalyticsData()
  }
})

watch(() => analyticsFilters.value.period, async (newPeriod) => {
  if (activeTab.value === 'analytics') {
    console.log('Period changed to:', newPeriod)
    await analyticsStore.fetchAnalyticsData()
  }
})

watch(() => analyticsFilters.value.metric, async (newMetric) => {
  if (activeTab.value === 'analytics') {
    console.log('Metric changed to:', newMetric)
    await analyticsStore.fetchAnalyticsData()
  }
})

watch(activeTab, async (newTab) => {
  if (newTab === 'analytics') {
    await analyticsStore.fetchAnalyticsData()
  } else if (newTab === 'reach') {
    await fetchReachData()
  } else if (newTab === 'fans') {
    await fetchTopFansData()
  }
})

// Watch for fan filter changes
watch(selectedFanFilter, async (newFilter) => {
  if (activeTab.value === 'fans') {
    await fetchTopFansData()
  }
})

</script>
  
  <style scoped>
  /* Import Remix Icons */
  @import url('https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css');
  
  /* Smooth transitions */
  * {
    transition-property: background-color, border-color, color, transform, box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }
  
  /* Custom scrollbar */
  ::-webkit-scrollbar {
    width: 4px;
  }
  
  ::-webkit-scrollbar-track {
    @apply bg-surface-light dark:bg-surface-dark;
  }
  
  ::-webkit-scrollbar-thumb {
    @apply bg-border-light dark:border-border-dark rounded-full;
  }
  
  /* Responsive text scaling */
  @media (max-width: 640px) {
    .text-responsive {
      font-size: clamp(0.875rem, 2.5vw, 1.125rem);
    }
  }
  </style>