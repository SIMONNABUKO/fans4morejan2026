<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Title -->
        <div class="flex flex-col">
          <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
            {{ t('subscriptions_view') }}
          </h1>
          <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
            Manage your creator subscriptions
          </p>
        </div>
        
        <!-- Right Side: Subscription Stats -->
        <div class="hidden md:flex items-center gap-6">
          <div class="text-center">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Active</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ tabCounts.active || 0 }}</div>
          </div>
          <div class="text-center">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ tabCounts.all || 0 }}</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Status</h2>
        <nav class="flex gap-8 overflow-x-auto scrollbar-hide">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="pb-4 relative whitespace-nowrap font-medium transition-colors duration-200"
            :class="[
              activeTab === tab.id 
                ? 'text-blue-600 dark:text-blue-400' 
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
            ]"
          >
            <div class="flex items-center gap-2">
              <span>{{ t(tab.id) }}</span>
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="activeTab === tab.id 
                      ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' 
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                {{ tabCounts[tab.id] || 0 }}
              </span>
            </div>
            <div 
              v-if="activeTab === tab.id"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"
            ></div>
          </button>
        </nav>
      </div>
    </div>

    <!-- Content Area -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center">
        <div class="w-12 h-12 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-gray-500 dark:text-gray-400">Loading subscriptions...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredSubscriptions.length === 0" class="p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-heart-line text-2xl text-gray-400 dark:text-gray-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Subscriptions Found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">{{ t('no_subscriptions_found') }}</p>
        <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-xl font-semibold transition-colors duration-200">
          Discover Creators
        </button>
      </div>

      <!-- Subscription Cards -->
      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <SubscriptionCard
            v-for="subscription in filteredSubscriptions"
            :key="subscription.id"
            :subscription="subscription"
            @renew="renewSubscription"
            @cancel="cancelSubscription"
            @toggle-block="toggleBlockCreator"
          />
        </div>
      </div>
    </div>

    <!-- Subscription Summary -->
    <div v-if="!loading && filteredSubscriptions.length > 0" class="mt-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Subscription Summary</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-heart-line text-green-600 dark:text-green-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ tabCounts.active || 0 }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Active Subscriptions</p>
        </div>
        
        <div class="text-center">
          <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-time-line text-orange-600 dark:text-orange-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ tabCounts.expired || 0 }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Expired Subscriptions</p>
        </div>
        
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-money-dollar-circle-line text-blue-600 dark:text-blue-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ totalSpent }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Total Spent</p>
        </div>
        
        <div class="text-center">
          <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-calendar-line text-purple-600 dark:text-purple-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ averageDuration }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Duration</p>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSubscriptionStore } from '@/stores/subscriptionStore'
import SubscriptionCard from '@/components/subscriptions/SubscriptionCard.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const subscriptionStore = useSubscriptionStore()
const activeTab = ref('active')
const loading = computed(() => subscriptionStore.loading)
const tabs = [
  {
    id: 'active',
    label: 'Active',
  },
  {
    id: 'expired',
    label: 'Expired',
  },
  {
    id: 'all',
    label: 'All',
  }
]

// Use computed property for tab counts to automatically update when store changes
const tabCounts = computed(() => subscriptionStore.subscriptionCounts)

// Fetch subscriptions when component mounts
onMounted(async () => {
  await subscriptionStore.fetchUserSubscriptions()
})

// Filter subscriptions based on active tab
const filteredSubscriptions = computed(() => {
  if (activeTab.value === 'all') {
    return subscriptionStore.allSubscriptions
  }
  return subscriptionStore.allSubscriptions.filter(sub => {
    if (activeTab.value === 'active') {
      return sub.status === 'completed'
    }
    if (activeTab.value === 'expired') {
      return sub.status === 'expired'
    }
    return true
  })
})

// Calculate summary statistics
const totalSpent = computed(() => {
  return filteredSubscriptions.value.reduce((total, sub) => {
    return total + (parseFloat(sub.price) || 0)
  }, 0).toFixed(2)
})

const averageDuration = computed(() => {
  if (filteredSubscriptions.value.length === 0) return '0 days'
  
  const totalDays = filteredSubscriptions.value.reduce((total, sub) => {
    // Calculate duration based on subscription data
    return total + 30 // Assuming average 30 days for now
  }, 0)
  
  const avgDays = Math.round(totalDays / filteredSubscriptions.value.length)
  return `${avgDays} days`
})

const renewSubscription = async (subscriptionId) => {
  try {
    const result = await subscriptionStore.renewSubscription(subscriptionId)
    if (result.success) {
      // Refresh subscriptions to update the UI
      await subscriptionStore.fetchUserSubscriptions()
    }
  } catch (error) {
    console.error('Error renewing subscription:', error)
  }
}

const cancelSubscription = async (subscriptionId) => {
  try {
    const result = await subscriptionStore.cancelSubscription(subscriptionId)
    if (result.success) {
      // Refresh subscriptions to update the UI
      await subscriptionStore.fetchUserSubscriptions()
    }
  } catch (error) {
    console.error('Error canceling subscription:', error)
  }
}

const toggleBlockCreator = async (creatorId, isCurrentlyBlocked) => {
  try {
    const result = await subscriptionStore.toggleBlockCreator(creatorId, isCurrentlyBlocked)
    if (result.success) {
      // Refresh subscriptions to update the UI
      await subscriptionStore.fetchUserSubscriptions()
    }
  } catch (error) {
    console.error('Error toggling creator block:', error)
  }
}
</script>

