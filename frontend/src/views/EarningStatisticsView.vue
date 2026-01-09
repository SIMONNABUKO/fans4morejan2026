<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 py-6">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <router-link 
              to="/dashboard" 
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ activeTab === 'wallet' ? t('earnings.wallet') : t('earnings.statistics') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Manage your earnings and financial data
              </p>
            </div>
          </div>
          
          <!-- Right Side: Quick Stats -->
          <div class="hidden md:flex items-center gap-6">
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Balance</div>
              <div class="text-lg font-bold text-green-600 dark:text-green-400">${{ formatCurrency(earningsStore.walletData?.total_balance || 0) }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Available</div>
              <div class="text-lg font-bold text-primary-light dark:text-primary-dark">${{ formatCurrency(earningsStore.walletData?.available_for_payout || 0) }}</div>
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
            <button 
              @click="activeTab = 'wallet'"
              :class="activeTab === 'wallet' 
                ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700'"
              class="flex-1 py-3 px-4 rounded-xl font-medium transition-all duration-200 text-center text-sm"
            >
              <div class="flex justify-center items-center gap-2">
                <i class="ri-wallet-3-line"></i>
                <span>{{ t('earnings.wallet') }}</span>
              </div>
            </button>
            <button 
              @click="activeTab = 'statistics'"
              :class="activeTab === 'statistics' 
                ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700'"
              class="flex-1 py-3 px-4 rounded-xl font-medium transition-all duration-200 text-center text-sm"
            >
              <div class="flex justify-center items-center gap-2">
                <i class="ri-bar-chart-line"></i>
                <span>{{ t('earnings.statistics') }}</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Wallet Tab Content -->
      <div v-if="activeTab === 'wallet'" class="space-y-8">
        <!-- Loading State -->
        <div v-if="earningsStore.walletLoading" class="flex justify-center items-center py-16">
          <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
            <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading wallet data...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="earningsStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Wallet</h3>
              <p class="text-red-600 dark:text-red-400">{{ earningsStore.error }}</p>
            </div>
          </div>
        </div>

        <template v-else>
          <!-- Wallet Balance Card -->
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('earnings.wallet') }}</h2>
              <button 
                @click="refreshWalletData" 
                class="p-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105"
                :class="{ 'animate-spin': refreshing }"
              >
                <i class="ri-refresh-line text-lg"></i>
              </button>
            </div>

            <div v-if="earningsStore.walletData" class="space-y-6">
              <!-- Total Balance -->
              <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-6 text-white">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm opacity-90">{{ t('earnings.total_balance') }}</p>
                    <p class="text-3xl font-bold">${{ formatCurrency(earningsStore.walletData.total_balance) }}</p>
                  </div>
                  <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-wallet-3-line text-2xl"></i>
                  </div>
                </div>
              </div>

              <!-- Balance Details -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('earnings.pending_balance') }}</span>
                      <i class="ri-time-line text-gray-400"></i>
                      <button 
                        @click="showPendingInfo = true" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                      >
                        <i class="ri-question-line"></i>
                      </button>
                    </div>
                  </div>
                  <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(earningsStore.walletData.pending_balance) }}</p>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('earnings.available_for_payout') }}</span>
                  </div>
                  <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(earningsStore.walletData.available_for_payout) }}</p>
                </div>
              </div>

              <!-- Request Payout Button -->
              <button 
                @click="showPayoutModal = true" 
                class="w-full bg-primary-light dark:bg-primary-dark text-white rounded-xl py-4 font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                :disabled="!canRequestPayout"
              >
                <div class="flex items-center justify-center gap-2">
                  <i class="ri-bank-card-line"></i>
                  <span>{{ t('earnings.request_payout') }}</span>
                </div>
              </button>
            </div>
            
            <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-wallet-3-line text-2xl"></i>
              </div>
              <p>{{ t('earnings.loading_wallet_data') }}</p>
            </div>
          </div>
        </template>
      </div>

      <!-- Statistics Tab Content -->
      <div v-if="activeTab === 'statistics'" class="space-y-8">
        <!-- Loading State -->
        <div v-if="earningsStore.statisticsLoading" class="flex justify-center items-center py-16">
          <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
            <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading statistics...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="earningsStore.error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Statistics</h3>
              <p class="text-red-600 dark:text-red-400">{{ earningsStore.error }}</p>
            </div>
          </div>
        </div>

        <template v-else>
          <!-- Statistics Summary -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Total Earnings</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">All time</p>
                </div>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(earningsStore.statistics?.total_earnings || 0) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-calendar-line text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">This Month</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Current period</p>
                </div>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(earningsStore.statistics?.monthly_earnings || 0) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                  <i class="ri-trending-up-line text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Growth</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">vs last month</p>
                </div>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ earningsStore.statistics?.growth_percentage || 0 }}%</p>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useEarningsStore } from '@/stores/earningsStore'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const earningsStore = useEarningsStore()

// State
const activeTab = ref('wallet')
const refreshing = ref(false)
const showPendingInfo = ref(false)

// Computed properties
const canRequestPayout = computed(() => {
  return earningsStore.walletData && 
         earningsStore.walletData.available_for_payout > 0
})

// Methods
const formatCurrency = (value) => {
  return parseFloat(value || 0).toFixed(2)
}

const refreshWalletData = async () => {
  refreshing.value = true
  try {
    await earningsStore.fetchWalletData()
  } catch (error) {
    console.error('Error refreshing wallet data:', error)
  } finally {
    refreshing.value = false
  }
}

// Lifecycle hooks
onMounted(async () => {
  if (activeTab.value === 'wallet') {
    await earningsStore.fetchWalletData()
  } else {
    await earningsStore.fetchEarningsStatistics()
  }
})

// Watch for tab changes
watch(activeTab, async (newTab) => {
  if (newTab === 'wallet') {
    await earningsStore.fetchWalletData()
  } else {
    await earningsStore.fetchEarningsStatistics()
  }
})
</script>

<style scoped>
/* Add any scoped styles here */
</style> 