<template>
  <div class="min-h-full">
    <div class="bg-white shadow">
      <div class="px-4 sm:px-6 lg:mx-auto lg:max-w-6xl lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
          <div class="min-w-0 flex-1">
            <div class="flex items-center">
              <div>
                <div class="flex items-center">
                  <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">Platform Wallet</h1>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8">
      <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <!-- Stats cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
              <div class="absolute rounded-md bg-indigo-500 p-3">
                <WalletIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <p class="ml-16 truncate text-sm font-medium text-gray-500">Current Balance</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
              <div v-if="loading.stats" class="animate-pulse h-8 w-24 bg-gray-200 rounded"></div>
              <p v-else class="text-2xl font-semibold text-gray-900">${{ Number(stats.current_balance || 0).toFixed(2) }}</p>
            </dd>
          </div>

          <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
              <div class="absolute rounded-md bg-indigo-500 p-3">
                <CurrencyDollarIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <p class="ml-16 truncate text-sm font-medium text-gray-500">Total Earnings</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
              <div v-if="loading.stats" class="animate-pulse h-8 w-24 bg-gray-200 rounded"></div>
              <p v-else class="text-2xl font-semibold text-gray-900">${{ Number(stats.total_earnings || 0).toFixed(2) }}</p>
            </dd>
          </div>

          <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
              <div class="absolute rounded-md bg-indigo-500 p-3">
                <CalendarIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <p class="ml-16 truncate text-sm font-medium text-gray-500">Today's Earnings</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
              <div v-if="loading.stats" class="animate-pulse h-8 w-24 bg-gray-200 rounded"></div>
              <p v-else class="text-2xl font-semibold text-gray-900">${{ Number(stats.today_earnings || 0).toFixed(2) }}</p>
            </dd>
          </div>

          <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
              <div class="absolute rounded-md bg-indigo-500 p-3">
                <ChartBarIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <p class="ml-16 truncate text-sm font-medium text-gray-500">This Month's Earnings</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
              <div v-if="loading.stats" class="animate-pulse h-8 w-24 bg-gray-200 rounded"></div>
              <p v-else class="text-2xl font-semibold text-gray-900">${{ Number(stats.month_earnings || 0).toFixed(2) }}</p>
            </dd>
          </div>
        </div>

        <!-- Transaction History -->
        <div class="mt-8">
          <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Transaction History</h3>
              <div class="mt-4">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-300">
                    <thead>
                      <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fee Type</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Original Amount</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fee %</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Users</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                          <span class="sr-only">Actions</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-if="loading.history">
                        <td colspan="9" class="py-4 text-center">
                          <div class="flex justify-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                          </div>
                        </td>
                      </tr>
                      <tr v-else-if="!history.data.length">
                        <td colspan="9" class="py-4 text-center text-sm text-gray-500">
                          No transaction history available
                        </td>
                      </tr>
                      <tr v-for="transaction in history.data" :key="transaction.id" class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                          {{ transaction.id }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ transaction.transaction_type }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ transaction.fee_type }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          ${{ Number(transaction.amount || 0).toFixed(2) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          ${{ Number(transaction.original_amount || 0).toFixed(2) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ Number(transaction.fee_percentage || 0).toFixed(2) }}%
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          <div v-if="transaction.sender">
                            <strong>From:</strong> {{ transaction.sender.display_name || transaction.sender.username }}<br>
                            <strong>To:</strong> {{ transaction.receiver ? (transaction.receiver.display_name || transaction.receiver.username) : 'N/A' }}
                          </div>
                          <div v-else>N/A</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                          {{ new Date(transaction.created_at).toLocaleString() }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                          <button @click="openTransactionDetails(transaction)" class="text-indigo-600 hover:text-indigo-900">
                            Details
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  WalletIcon,
  CurrencyDollarIcon,
  CalendarIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'
import axios from '@/plugins/axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const stats = ref({
  current_balance: 0,
  total_earnings: 0,
  today_earnings: 0,
  month_earnings: 0
})

const history = ref({
  data: [],
  meta: {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  }
})

const loading = ref({
  stats: false,
  history: false
})

const fetchStats = async () => {
  loading.value.stats = true
  try {
    const response = await axios.get('/admin/platform-wallet/stats')
    if (response.data.success) {
      stats.value = {
        current_balance: Number(response.data.data.current_balance || 0),
        total_earnings: Number(response.data.data.total_earnings || 0),
        today_earnings: Number(response.data.data.today_earnings || 0),
        month_earnings: Number(response.data.data.month_earnings || 0)
      }
    } else {
      toast.error('Failed to fetch wallet statistics')
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
    toast.error('Failed to fetch wallet statistics')
  } finally {
    loading.value.stats = false
  }
}

const fetchHistory = async () => {
  loading.value.history = true
  try {
    const response = await axios.get('/admin/platform-wallet/history')
    if (response.data.success) {
      history.value = {
        data: response.data.data,
        meta: response.data.meta
      }
    } else {
      toast.error('Failed to fetch transaction history')
    }
  } catch (error) {
    console.error('Error fetching history:', error)
    toast.error('Failed to fetch transaction history')
  } finally {
    loading.value.history = false
  }
}

const openTransactionDetails = (transaction) => {
  // Implementation for transaction details modal
  console.log('Transaction details:', transaction)
}

onMounted(async () => {
  await Promise.all([
    fetchStats(),
    fetchHistory()
  ])
})
</script>

<style scoped>
.table th {
  white-space: nowrap;
}
</style> 