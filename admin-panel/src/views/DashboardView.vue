<template>
  <div class="min-h-full">
    <div class="py-6 w-full">
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
      </div>
      <div class="px-4 sm:px-6 lg:px-8">
        <!-- Stats -->
        <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <template v-if="isLoading">
            <div v-for="i in 4" :key="i" class="animate-pulse relative overflow-hidden rounded-lg bg-white p-5 shadow dark:bg-gray-800">
              <dt>
                <div class="absolute rounded-md bg-gray-200 dark:bg-gray-700 p-3">
                  <div class="h-6 w-6"></div>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-400">Loading...</p>
              </dt>
              <dd class="ml-16 flex items-baseline">
                <div class="h-7 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
              </dd>
            </div>
          </template>

          <template v-else>
            <div v-for="(stat, key) in statsData" :key="key" class="relative overflow-hidden rounded-lg bg-white p-5 shadow dark:bg-gray-800">
              <dt>
                <div class="absolute rounded-md bg-indigo-50 p-3 dark:bg-indigo-900/50">
                  <component :is="stat.icon" class="h-6 w-6 text-indigo-600 dark:text-indigo-200" aria-hidden="true" />
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ stat.label }}</p>
              </dt>
              <dd class="ml-16 flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ stat.value }}</p>
              </dd>
            </div>
          </template>
        </dl>

        <!-- Recent Activity -->
        <div class="mt-8">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h2>
          <div class="mt-4 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <ul v-if="isLoading" role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
              <li v-for="i in 3" :key="i" class="animate-pulse px-6 py-4">
                <div class="flex items-center space-x-4">
                  <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                  <div class="flex-1">
                    <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                    <div class="h-3 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                  </div>
                  <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
              </li>
            </ul>

            <ul v-else role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
              <li v-for="activity in recentActivity" :key="activity.id" class="px-6 py-4">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <div class="rounded-full bg-indigo-50 p-2 dark:bg-indigo-900/50">
                      <component :is="getActivityIcon(activity.type)" class="h-5 w-5 text-indigo-600 dark:text-indigo-200" />
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                  </div>
                  <div class="flex-shrink-0">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatTimestamp(activity.timestamp) }}</span>
                  </div>
                </div>
              </li>
            </ul>

            <div v-if="error" class="p-4 text-sm text-red-600 dark:text-red-400">
              {{ error }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import type { DashboardStats, ActivityItem } from '@/stores/dashboard'
import {
  UsersIcon,
  DocumentTextIcon,
  ChatBubbleLeftIcon,
  CurrencyDollarIcon,
  UserPlusIcon,
  CreditCardIcon,
  DocumentPlusIcon
} from '@heroicons/vue/24/outline'
import { formatDistanceToNow } from 'date-fns'
import { storeToRefs } from 'pinia'
import axios from '@/plugins/axios'

const store = useDashboardStore()
const { stats, recentActivity, isLoading, error } = storeToRefs(store)

interface DashboardStat {
  label: string
  value: string
  icon: any // Using any for now since HeroIcon types are complex
}

const statsData = computed<DashboardStat[]>(() => {
  if (!stats.value) {
    return [
      { label: 'Total Users', value: '0', icon: UsersIcon },
      { label: 'Platform Balance', value: '$0.00', icon: CurrencyDollarIcon },
      { label: 'Active Posts', value: '0', icon: DocumentTextIcon },
      { label: 'Comments', value: '0', icon: ChatBubbleLeftIcon }
    ]
  }
  
  const { totalUsers, activePosts, totalComments, platformBalance } = stats.value
  
  return [
    { label: 'Total Users', value: totalUsers.toLocaleString(), icon: UsersIcon },
    { label: 'Platform Balance', value: `$${Number(platformBalance || 0).toFixed(2)}`, icon: CurrencyDollarIcon },
    { label: 'Active Posts', value: activePosts.toLocaleString(), icon: DocumentTextIcon },
    { label: 'Comments', value: totalComments.toLocaleString(), icon: ChatBubbleLeftIcon }
  ]
})

const getActivityIcon = (type: string) => {
  switch (type) {
    case 'user_registration':
      return UserPlusIcon
    case 'subscription':
      return CreditCardIcon
    case 'post_published':
      return DocumentPlusIcon
    default:
      return DocumentTextIcon
  }
}

const formatTimestamp = (timestamp: string) => {
  return formatDistanceToNow(new Date(timestamp), { addSuffix: true })
}

onMounted(async () => {
  try {
    const [dashboardStats, activityData, walletData] = await Promise.all([
      store.fetchDashboardStats(),
      store.fetchRecentActivity(),
      axios.get('/admin/platform-wallet/stats')
    ])

    // Update platform balance in stats
    if (walletData?.data?.success && stats.value) {
      stats.value = {
        totalUsers: stats.value.totalUsers || 0,
        activePosts: stats.value.activePosts || 0,
        totalComments: stats.value.totalComments || 0,
        platformBalance: Number(walletData.data.data.current_balance || 0)
      }
    }
  } catch (e) {
    console.error('Failed to load dashboard data:', e)
  }
})
</script> 