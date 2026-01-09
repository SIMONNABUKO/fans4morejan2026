<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Transaction Monitoring</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitor and manage all platform transactions</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="stat in stats" :key="stat.name" 
           class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
        <div class="text-sm text-gray-600 dark:text-gray-400">{{ stat.name }}</div>
        <div class="mt-1 text-2xl font-semibold">
          {{ stat.value }}
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <!-- Transaction Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Transaction Type
          </label>
          <select v-model="filters.type" 
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="">All Types</option>
            <option value="one_time_purchase">One Time Purchase</option>
            <option value="one_month_subscription">One Month Subscription</option>
            <option value="three_months_subscription">Three Months Subscription</option>
            <option value="two_months_subscription">Two Months Subscription</option>
            <option value="six_months_subscription">Six Months Subscription</option>
            <option value="yearly_subscription">Yearly Subscription</option>
            <option value="tip">Tip</option>
            <option value="subscription_renewal">Subscription Renewal</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Status
          </label>
          <select v-model="filters.status" 
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="declined">Declined</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <!-- Date Range Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Date Range
          </label>
          <input type="date" v-model="filters.startDate" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 mb-2">
          <input type="date" v-model="filters.endDate" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
        </div>

        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Search
          </label>
          <input type="text" v-model="filters.search" 
                 placeholder="Search by ID or user..." 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
          <tr>
            <th v-for="header in tableHeaders" :key="header" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
              {{ header }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="8" class="px-6 py-4">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
            </td>
          </tr>
          <tr v-else-if="!transactions.length" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              No transactions found
            </td>
          </tr>
          <tr v-for="transaction in transactions" :key="transaction?.id" 
              class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
              {{ transaction?.id || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ transaction?.formatted_type || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ transaction?.formatted_amount || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ transaction?.sender?.username || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ transaction?.receiver?.username || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(transaction?.status)" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ transaction?.status || 'Unknown' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ transaction?.created_at ? formatDate(transaction.created_at) : '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="openTransactionDetails(transaction)" 
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                Details
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 
                  sm:px-6 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <button @click="prevPage" :disabled="currentPage === 1" 
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 
                         text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Previous
          </button>
          <button @click="nextPage" :disabled="!hasMorePages" 
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 
                         text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-400">
              Showing
              <span class="font-medium">{{ paginationStart }}</span>
              to
              <span class="font-medium">{{ paginationEnd }}</span>
              of
              <span class="font-medium">{{ totalTransactions }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button @click="prevPage" 
                      :disabled="currentPage === 1"
                      :class="[
                        currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50',
                        'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500'
                      ]">
                Previous
              </button>
              <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                Page {{ currentPage }} of {{ lastPage }}
              </span>
              <button @click="nextPage" 
                      :disabled="!hasMorePages"
                      :class="[
                        !hasMorePages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50',
                        'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500'
                      ]">
                Next
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="closeTransactionDetails" class="relative z-10">
        <TransitionChild
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              enter="ease-out duration-300"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel 
                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 
                       p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle 
                  class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                  Transaction Details
                </DialogTitle>

                <div class="mt-4">
                  <div v-if="selectedTransaction" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaction ID</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTransaction.id }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTransaction.formatted_type }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTransaction.formatted_amount }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                        <p class="text-sm">
                          <span :class="getStatusClass(selectedTransaction.status)" 
                                class="px-2 py-1 text-xs font-medium rounded-full">
                            {{ selectedTransaction.status }}
                          </span>
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">From</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTransaction.sender?.username || 'N/A' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">To</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTransaction.receiver?.username || 'N/A' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(selectedTransaction.created_at) }}</p>
                      </div>
                      <div v-if="selectedTransaction.refunded_at">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Refunded At</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(selectedTransaction.refunded_at) }}</p>
                      </div>
                    </div>

                    <div v-if="selectedTransaction.additional_data" class="mt-4">
                      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Additional Information</p>
                      <div class="bg-gray-50 dark:bg-gray-700 rounded p-3">
                        <div v-for="(item, index) in formatAdditionalData(selectedTransaction.additional_data)" 
                             :key="index"
                             class="grid grid-cols-2 gap-2 mb-1 last:mb-0">
                          <span class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</span>
                          <span class="text-sm text-gray-900 dark:text-white">{{ item.value }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                  <button
                    v-if="canRefund"
                    @click="handleRefund"
                    class="inline-flex justify-center rounded-md border border-transparent bg-red-100 
                           px-4 py-2 text-sm font-medium text-red-900 hover:bg-red-200 
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 
                           focus-visible:ring-offset-2"
                  >
                    Refund
                  </button>
                  <button
                    @click="closeTransactionDetails"
                    class="inline-flex justify-center rounded-md border border-transparent 
                           bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 
                           hover:bg-blue-200 focus:outline-none focus-visible:ring-2 
                           focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                  >
                    Close
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useToast } from 'vue-toastification'
import axios from '@/plugins/axios'

interface Transaction {
  id: number
  type: string
  amount: number
  formatted_type: string
  formatted_amount: string
  sender?: {
    username: string
  }
  receiver?: {
    username: string
  }
  status: 'pending' | 'approved' | 'declined' | 'refunded'
  created_at: string
  refunded_at?: string
  additional_data?: AdditionalData
  refunded: boolean
}

interface TransactionStat {
  name: string
  value: string | number
}

interface Stats {
  total_transactions: number
  total_amount: number
  completed_transactions: number
  pending_transactions: number
}

interface AdditionalData {
  [key: string]: any
  post_id?: string
  tippable_id?: string
  tippable_type?: string
  payment_method?: string
  subscription_id?: string
  duration?: string
  media_id?: string
  media_type?: string
}

interface Filters {
  type: string
  status: string
  startDate: string
  endDate: string
  search: string
}

interface TransactionResponse {
  data: Transaction[]
  meta: {
    current_page: number
    last_page: number
    total: number
  }
  stats: Stats
}

// Constants
const perPage = 10

// State
const transactions = ref<Transaction[]>([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const totalTransactions = ref(0)
const isModalOpen = ref(false)
const selectedTransaction = ref<Transaction | null>(null)
const toast = useToast()

const stats = computed<TransactionStat[]>(() => [
  { name: 'Total Transactions', value: statsData.value.total_transactions },
  { name: 'Total Amount', value: formatAmount(statsData.value.total_amount) },
  { name: 'Completed Transactions', value: statsData.value.completed_transactions },
  { name: 'Pending Transactions', value: statsData.value.pending_transactions }
])

const statsData = ref<Stats>({
  total_transactions: 0,
  total_amount: 0,
  completed_transactions: 0,
  pending_transactions: 0
})

const filters = reactive<Filters>({
  type: '',
  status: '',
  startDate: '',
  endDate: '',
  search: ''
})

const tableHeaders = [
  'ID',
  'Type',
  'Amount',
  'From',
  'To',
  'Status',
  'Date',
  'Actions'
]

// Computed
const paginationStart = computed(() => {
  if (totalTransactions.value === 0) return 0
  return ((currentPage.value - 1) * perPage) + 1
})

const paginationEnd = computed(() => {
  return Math.min(currentPage.value * perPage, totalTransactions.value)
})

const hasMorePages = computed(() => currentPage.value < lastPage.value)
const canRefund = computed(() => {
  return selectedTransaction.value && 
         selectedTransaction.value.status === 'approved' && 
         !selectedTransaction.value.refunded_at
})

// Methods
const formatTransactionType = (type: string): string => {
  const types = {
    one_time_purchase: 'One Time Purchase',
    one_month_subscription: 'One Month Subscription',
    three_months_subscription: 'Three Months Subscription',
    two_months_subscription: 'Two Months Subscription',
    six_months_subscription: 'Six Months Subscription',
    yearly_subscription: 'Yearly Subscription',
    tip: 'Tip',
    subscription_renewal: 'Subscription Renewal'
  } as const

  return types[type as keyof typeof types] || type
}

const formatAmount = (amount: number): string => {
  return `$${Number(amount).toFixed(2)}`
}

const fetchTransactions = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/transactions', {
      params: {
        page: currentPage.value,
        per_page: perPage,
        ...filters
      }
    })

    // Handle Laravel pagination response
    const { data, meta } = response.data
    transactions.value = (data.data || []).map((transaction: Transaction) => ({
      ...transaction,
      formatted_type: formatTransactionType(transaction.type),
      formatted_amount: formatAmount(transaction.amount)
    }))
    totalTransactions.value = data.total || 0
    currentPage.value = data.current_page || 1
    lastPage.value = data.last_page || 1
    
    // Update stats
    updateStats(meta?.stats || {})
  } catch (error) {
    toast.error('Failed to fetch transactions')
    console.error('Error fetching transactions:', error)
    transactions.value = []
    totalTransactions.value = 0
    updateStats({})
  } finally {
    loading.value = false
  }
}

const updateStats = (newStats: Partial<Stats> = {}) => {
  statsData.value = {
    total_transactions: Number(newStats.total_transactions || 0),
    total_amount: Number(newStats.total_amount || 0),
    completed_transactions: Number(newStats.completed_transactions || 0),
    pending_transactions: Number(newStats.pending_transactions || 0)
  }
}

const getStatusClass = (status: Transaction['status']) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    declined: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    refunded: 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300'
  }
  return classes[status] || ''
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString()
}

const capitalizeWords = (key: string): string => {
  return key.split('_').map((word: string) => {
    return word.charAt(0).toUpperCase() + word.slice(1)
  }).join(' ')
}

const formatValue = (key: string, value: any) => {
  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  return String(value)
}

const formatAdditionalData = (data: AdditionalData) => {
  if (!data) return null
  
  const labels: Record<string, string> = {
    post_id: 'Post ID',
    tippable_id: 'Tippable ID',
    tippable_type: 'Tippable Type',
    payment_method: 'Payment Method',
    subscription_id: 'Subscription ID',
    duration: 'Duration',
    media_id: 'Media ID',
    media_type: 'Media Type'
  }

  const formatValue = (key: string, value: any) => {
    if (key === 'payment_method') {
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
    if (key === 'tippable_type' || key === 'media_type') {
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
    return value
  }

  return Object.entries(data).map(([key, value]) => ({
    label: labels[key] || key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
    value: formatValue(key, value)
  }))
}

const openTransactionDetails = (transaction: Transaction) => {
  selectedTransaction.value = transaction
  isModalOpen.value = true
}

const closeTransactionDetails = () => {
  selectedTransaction.value = null
  isModalOpen.value = false
}

const handleRefund = async () => {
  if (!selectedTransaction.value) return

  try {
    await axios.post(`/admin/transactions/${selectedTransaction.value.id}/refund`)
    toast.success('Transaction refunded successfully')
    fetchTransactions()
    closeTransactionDetails()
  } catch (error) {
    toast.error('Failed to refund transaction')
    console.error('Error refunding transaction:', error)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchTransactions()
  }
}

const nextPage = () => {
  if (hasMorePages.value) {
    currentPage.value++
    fetchTransactions()
  }
}

// Watch filters
watch(filters, () => {
  currentPage.value = 1
  fetchTransactions()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchTransactions()
})
</script> 