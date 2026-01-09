<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Creator Tier Management</h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitor and manage creator subscription tiers</p>
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
        <!-- Price Range Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Price Range
          </label>
          <div class="flex space-x-2">
            <input type="number" v-model="filters.minPrice" 
                   placeholder="Min" 
                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <input type="number" v-model="filters.maxPrice" 
                   placeholder="Max" 
                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
          </div>
        </div>

        <!-- Creator Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Creator
          </label>
          <input type="text" v-model="filters.creator" 
                 placeholder="Search by creator name" 
                 class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Status
          </label>
          <select v-model="filters.status" 
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending Review</option>
          </select>
        </div>

        <!-- Sort By -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Sort By
          </label>
          <select v-model="filters.sortBy" 
                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
            <option value="subscribers">Subscribers</option>
            <option value="price">Price</option>
            <option value="revenue">Revenue</option>
            <option value="created">Date Created</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tiers Table -->
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
          <tr v-else-if="!tiers.length" class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              No tiers found
            </td>
          </tr>
          <tr v-for="tier in tiers" :key="tier.id" 
              class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
              {{ tier.title }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ tier.creator.username }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              ${{ tier.pricing?.base_price?.toFixed(2) || '0.00' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ tier.subscribers_count }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              ${{ tier.monthly_revenue?.toFixed(2) || '0.00' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(tier.status)" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ tier.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(tier.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="openTierDetails(tier)" 
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
              <span class="font-medium">{{ totalTiers }}</span>
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

    <!-- Tier Details Modal -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="closeTierDetails" class="relative z-10">
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
                  Tier Details
                </DialogTitle>

                <div class="mt-4">
                  <div v-if="selectedTier" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Title</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTier.title }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Creator</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTier.creator.username }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Base Price</p>
                        <p class="text-sm text-gray-900 dark:text-white">${{ selectedTier?.pricing?.base_price?.toFixed(2) || '0.00' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                        <p class="text-sm">
                          <span :class="getStatusClass(selectedTier.status)" 
                                class="px-2 py-1 text-xs font-medium rounded-full">
                            {{ selectedTier.status }}
                          </span>
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscribers</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ selectedTier.subscribers_count }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                        <p class="text-sm text-gray-900 dark:text-white">${{ selectedTier?.monthly_revenue?.toFixed(2) || '0.00' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(selectedTier.created_at) }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(selectedTier.updated_at) }}</p>
                      </div>
                    </div>

                    <div class="mt-4">
                      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</p>
                      <p class="text-sm text-gray-900 dark:text-white">{{ selectedTier.description }}</p>
                    </div>

                    <div class="mt-4">
                      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Perks</p>
                      <ul class="list-disc list-inside text-sm text-gray-900 dark:text-white">
                        <li v-for="(perk, index) in selectedTier.perks" :key="index">
                          {{ perk }}
                        </li>
                      </ul>
                    </div>

                    <!-- Add pricing options section -->
                    <div class="col-span-2 mt-4">
                      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Pricing Options</p>
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <p class="text-xs text-gray-500">2 Month Price</p>
                          <p class="text-sm">${{ selectedTier?.pricing?.two_month_price?.toFixed(2) || '0.00' }}</p>
                          <p class="text-xs text-gray-400">Discount: {{ selectedTier?.pricing?.two_month_discount || 0 }}%</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">3 Month Price</p>
                          <p class="text-sm">${{ selectedTier?.pricing?.three_month_price?.toFixed(2) || '0.00' }}</p>
                          <p class="text-xs text-gray-400">Discount: {{ selectedTier?.pricing?.three_month_discount || 0 }}%</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">6 Month Price</p>
                          <p class="text-sm">${{ selectedTier?.pricing?.six_month_price?.toFixed(2) || '0.00' }}</p>
                          <p class="text-xs text-gray-400">Discount: {{ selectedTier?.pricing?.six_month_discount || 0 }}%</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                  <button
                    v-if="selectedTier?.status === 'pending'"
                    @click="updateTierStatus('active')"
                    class="inline-flex justify-center rounded-md border border-transparent bg-green-100 
                           px-4 py-2 text-sm font-medium text-green-900 hover:bg-green-200 
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 
                           focus-visible:ring-offset-2"
                  >
                    Approve
                  </button>
                  <button
                    v-if="selectedTier?.status === 'active'"
                    @click="updateTierStatus('inactive')"
                    class="inline-flex justify-center rounded-md border border-transparent bg-red-100 
                           px-4 py-2 text-sm font-medium text-red-900 hover:bg-red-200 
                           focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 
                           focus-visible:ring-offset-2"
                  >
                    Deactivate
                  </button>
                  <button
                    @click="closeTierDetails"
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useToast } from 'vue-toastification'
import axios from '@/plugins/axios'
import { formatDate } from '@/utils/date'

interface Creator {
  username: string;
}

interface Tier {
  id: number;
  title: string;
  description: string;
  pricing: {
    base_price: number;
    two_month_price: number;
    three_month_price: number;
    six_month_price: number;
    two_month_discount: number;
    three_month_discount: number;
    six_month_discount: number;
  };
  status: 'active' | 'inactive' | 'pending';
  creator: Creator;
  subscribers_count: number;
  monthly_revenue: number;
  created_at: string;
  updated_at: string;
  perks: string[];
}

interface TierStats {
  total_tiers: number;
  active_tiers: number;
  total_subscribers: number;
  monthly_revenue: number;
}

// State
const tiers = ref<Tier[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalTiers = ref(0)
const lastPage = ref(1)
const perPage = 10
const isModalOpen = ref(false)
const selectedTier = ref<Tier | null>(null)
const toast = useToast()

const stats = ref([
  { name: 'Total Tiers', value: '0' },
  { name: 'Active Tiers', value: '0' },
  { name: 'Total Subscribers', value: '0' },
  { name: 'Monthly Revenue', value: '$0' }
])

const filters = ref({
  minPrice: '',
  maxPrice: '',
  creator: '',
  status: '',
  sortBy: 'created'
})

const tableHeaders = [
  'Title',
  'Creator',
  'Price',
  'Subscribers',
  'Monthly Revenue',
  'Status',
  'Created At',
  'Actions'
]

// Computed
const paginationStart = computed(() => {
  if (totalTiers.value === 0) return 0
  return ((currentPage.value - 1) * perPage) + 1
})

const paginationEnd = computed(() => {
  return Math.min(currentPage.value * perPage, totalTiers.value)
})

const hasMorePages = computed(() => currentPage.value < lastPage.value)

// Methods
const fetchTiers = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/tiers', {
      params: {
        page: currentPage.value,
        ...filters.value
      }
    })

    if (response.data.success) {
      tiers.value = response.data.data.data
      currentPage.value = response.data.data.current_page
      lastPage.value = response.data.data.last_page
      totalTiers.value = response.data.data.total
      
      // Update stats
      const statsData = response.data.meta.stats
      stats.value = [
        { name: 'Total Tiers', value: statsData.total_tiers },
        { name: 'Active Tiers', value: statsData.active_tiers },
        { name: 'Total Subscribers', value: statsData.total_subscribers },
        { name: 'Monthly Revenue', value: `$${statsData.monthly_revenue}` }
      ]
    } else {
      toast.error('Failed to fetch tiers')
    }
  } catch (error) {
    console.error('Error fetching tiers:', error)
    toast.error('Failed to fetch tiers')
  } finally {
    loading.value = false
  }
}

const getStatusClass = (status: 'active' | 'inactive' | 'pending'): string => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800'
  } as const;
  return classes[status] || 'bg-gray-100 text-gray-800';
}

const openTierDetails = (tier: Tier): void => {
  selectedTier.value = tier;
  isModalOpen.value = true;
}

const closeTierDetails = (): void => {
  selectedTier.value = null;
  isModalOpen.value = false;
}

const updateTierStatus = async (newStatus: 'active' | 'inactive'): Promise<void> => {
  if (!selectedTier.value) return;

  try {
    const response = await axios.patch(`/admin/tiers/${selectedTier.value.id}/status`, {
      status: newStatus
    });

    if (response.data.success) {
      toast.success(`Tier ${newStatus === 'active' ? 'approved' : 'deactivated'} successfully`);
      fetchTiers();
      closeTierDetails();
    } else {
      toast.error('Failed to update tier status');
    }
  } catch (error) {
    console.error('Error updating tier status:', error);
    toast.error('Failed to update tier status');
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchTiers()
  }
}

const nextPage = () => {
  if (hasMorePages.value) {
    currentPage.value++
    fetchTiers()
  }
}

// Watch filters
watch(filters, () => {
  currentPage.value = 1
  fetchTiers()
}, { deep: true })

// Lifecycle
onMounted(() => {
  fetchTiers()
})
</script> 