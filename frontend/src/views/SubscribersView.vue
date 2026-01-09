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
                {{ t('subscribers') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Manage your subscriber relationships and insights
              </p>
            </div>
          </div>
          
          <!-- Right Side: Quick Stats -->
          <div class="hidden md:flex items-center gap-6">
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Active</div>
              <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ subscriberCounts.active }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total</div>
              <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ subscriberCounts.all }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="space-y-6">
        <!-- Enhanced Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-2">
          <nav class="flex gap-2">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="changeTab(tab.id)"
              class="flex-1 py-3 px-4 rounded-xl font-medium text-sm transition-all duration-200 relative"
              :class="[
                activeTab === tab.id 
                  ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' 
                  : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700'
              ]"
            >
              <div class="flex items-center justify-center gap-2">
                <span>{{ t(tab.id) }}</span>
                <span class="bg-white/20 dark:bg-gray-800/20 px-2 py-0.5 rounded-full text-xs font-semibold">
                  {{ subscriberCounts[tab.id] }}
                </span>
              </div>
            </button>
          </nav>
        </div>

        <!-- Enhanced Search -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="ri-search-line text-text-light-tertiary dark:text-text-dark-tertiary text-lg"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            @input="handleSearch"
            :placeholder="t('search_for_subscriber')"
            class="w-full pl-12 pr-4 py-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200"
          />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-16">
          <div class="flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
            <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading subscribers...</p>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!hasSubscribers" class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
            <i class="ri-user-search-line text-3xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ searchQuery ? 'No subscribers found' : 'No subscribers yet' }}
          </h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">
            {{ searchQuery ? t('no_subscribers_match_search') : 'Start building your subscriber base by creating engaging content and subscription tiers.' }}
          </p>
          <button 
            v-if="searchQuery"
            @click="clearSearch"
            class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-refresh-line"></i>
            {{ t('clear_search') }}
          </button>
        </div>

        <!-- Enhanced Subscribers Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="subscriber in subscribers" 
            :key="subscriber.id"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-300 hover:scale-[1.02] group"
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="relative">
                  <img 
                    :src="subscriber.avatar" 
                    :alt="subscriber.username"
                    class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700 group-hover:ring-primary-light dark:group-hover:ring-primary-dark transition-all duration-200"
                  />
                  <div v-if="subscriber.isVip" class="absolute -top-1 -right-1 w-5 h-5 bg-yellow-500 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center">
                    <i class="ri-vip-crown-fill text-white text-xs"></i>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <button 
                    @click="$router.push(`/${subscriber.username}/posts`)"
                    class="font-semibold text-gray-900 dark:text-white truncate hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer block w-full text-left"
                  >
                    {{ subscriber.username }}
                  </button>
                  <button 
                    @click="$router.push(`/${subscriber.username}/posts`)"
                    class="text-sm text-gray-500 dark:text-gray-400 truncate hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer block w-full text-left"
                  >
                    @{{ subscriber.handle }}
                  </button>
                </div>
              </div>
              <SubscriberActionsMenu 
                :subscriber-id="subscriber.subscriberId"
                :is-vip="subscriber.isVip"
                :is-muted="subscriber.isMuted"
                @action="handleSubscriberAction"
              />
            </div>

            <!-- Subscription Details -->
            <div class="space-y-3">
              <!-- Tier Info -->
              <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-xl">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-vip-crown-line text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('subscription_tier') }} {{ subscriber.tier }}</div>
                  <div v-if="subscriber.emoji" class="text-lg">{{ subscriber.emoji }}</div>
                </div>
              </div>

              <!-- Renewal Info -->
              <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-refresh-line text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('renew') }} {{ subscriber.renew }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ t('billing_cycle') }} {{ subscriber.billingCycle }}</div>
                </div>
              </div>

              <!-- Expiry Info -->
              <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl">
                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-time-line text-orange-600 dark:text-orange-400 text-lg"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('subscription_expired_at') }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ subscriber.expiredAt }}</div>
                </div>
              </div>

              <!-- Price Info -->
              <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl">
                <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-money-dollar-circle-line text-emerald-600 dark:text-emerald-400 text-lg"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ t('subscription_price') }}</div>
                  <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${{ subscriber.price }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-8">
          <button 
            class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-all duration-200"
            :class="currentPage === 1 ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'text-primary-light dark:text-primary-dark hover:bg-primary-light/10 dark:hover:bg-primary-dark/10 hover:scale-105'"
            :disabled="currentPage === 1"
            @click="changePage(currentPage - 1)"
          >
            <i class="ri-arrow-left-s-line text-lg"></i>
          </button>
          
          <template v-for="page in totalPages" :key="page">
            <button 
              v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
              @click="changePage(page)"
              class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-all duration-200"
              :class="currentPage === page ? 'bg-primary-light dark:bg-primary-dark text-white shadow-lg' : 'text-text-light-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-700 hover:scale-105'"
            >
              {{ page }}
            </button>
            <span 
              v-else-if="page === currentPage - 2 || page === currentPage + 2"
              class="text-text-light-tertiary dark:text-text-dark-tertiary px-2"
            >
              ...
            </span>
          </template>

          <button 
            class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-all duration-200"
            :class="currentPage === totalPages ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'text-primary-light dark:text-primary-dark hover:bg-primary-light/10 dark:hover:bg-primary-dark/10 hover:scale-105'"
            :disabled="currentPage === totalPages"
            @click="changePage(currentPage + 1)"
          >
            <i class="ri-arrow-right-s-line text-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import SubscriberActionsMenu from '@/components/subscribers/SubscriberActionsMenu.vue'
import { useToast } from '@/composables/useToast'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Dynamic import for subscribers store
let subscribersStore = null

const toast = useToast()

// Local state
const activeTab = ref('all')
const searchQuery = ref('')
const searchTimeout = ref(null)

// Computed properties
const loading = computed(() => subscribersStore?.loading || false)
const subscribers = computed(() => subscribersStore?.subscribers || [])
const hasSubscribers = computed(() => subscribersStore?.hasSubscribers || false)
const currentPage = computed(() => subscribersStore?.pagination?.currentPage || 1)
const totalPages = computed(() => subscribersStore?.pagination?.totalPages || 1)
const subscriberCounts = computed(() => subscribersStore?.counts || {})

const tabs = [
  { id: 'active', label: 'Active' },
  { id: 'expired', label: 'Expired' },
  { id: 'all', label: 'All' }
]

// Fetch subscribers when component mounts
onMounted(async () => {
  const { useSubscribersStore } = await import('@/stores/subscribersStore')
  subscribersStore = useSubscribersStore()
  await Promise.all([
    fetchSubscribers(),
    subscribersStore.fetchSubscriberCounts()
  ])
})

// Methods
const fetchSubscribers = async () => {
  if (!subscribersStore) {
    const { useSubscribersStore } = await import('@/stores/subscribersStore')
    subscribersStore = useSubscribersStore()
  }
  await subscribersStore.fetchSubscribers(
    subscribersStore.pagination.currentPage,
    activeTab.value,
    searchQuery.value
  )
}

const changeTab = async (tabId) => {
  activeTab.value = tabId
  await fetchSubscribers()
}

const changePage = async (page) => {
  await subscribersStore.fetchSubscribers(page, activeTab.value, searchQuery.value)
}

const handleSearch = () => {
  // Debounce search input
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  searchTimeout.value = setTimeout(async () => {
    await fetchSubscribers()
  }, 300)
}

const clearSearch = () => {
  searchQuery.value = ''
  fetchSubscribers()
}

const handleSubscriberAction = async ({ subscriberId, action }) => {
  try {
    switch (action) {
      case 'vip':
        const vipResult = await subscribersStore.toggleVipStatus(subscriberId)
        if (vipResult.success) {
          toast.success(vipResult.message)
        } else {
          toast.error(vipResult.error)
        }
        break
        
      case 'mute':
        const muteResult = await subscribersStore.toggleMuteStatus(subscriberId)
        if (muteResult.success) {
          toast.success(muteResult.message)
        } else {
          toast.error(muteResult.error)
        }
        break
        
      case 'block':
        const blockResult = await subscribersStore.blockSubscriber(subscriberId)
        if (blockResult.success) {
          toast.success(blockResult.message)
          // Refresh counts after blocking
          await subscribersStore.fetchSubscriberCounts()
        } else {
          toast.error(blockResult.error)
        }
        break
        
      case 'earnings':
        const earningsResult = await subscribersStore.getSubscriberEarnings(subscriberId)
        if (earningsResult.success) {
          toast.info(`Total earnings from this subscriber: $${earningsResult.data.total_earnings}`)
        } else {
          toast.error(earningsResult.error)
        }
        break
        
      case 'addToList':
        // This would typically open a modal to select lists
        toast.info('Add to list feature coming soon')
        break
        
      case 'report':
        // This would typically open a report form
        toast.info('Report feature coming soon')
        break
    }
  } catch (error) {
    console.error('Error handling subscriber action:', error)
    toast.error('An error occurred while processing your request')
  }
}
</script>
  
  