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
              Wallet
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your finances and payment methods
            </p>
          </div>
        </div>
        
        <!-- Right Side: Wallet Status -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Total Balance</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">
              ${{ formatCurrency(walletData.total_balance || 0) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs (Creators Only) -->
    <div v-if="isCreator" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <nav class="flex gap-8 -mb-px">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="pb-4 relative font-medium transition-colors duration-200"
          :class="[
            activeTab === tab.id 
              ? 'text-blue-600 dark:text-blue-400' 
              : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
          ]"
        >
          <div class="flex items-center gap-2">
            <i :class="tab.icon"></i>
            {{ tab.label }}
          </div>
          <div 
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full"
          ></div>
        </button>
      </nav>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Wallet Balance Section (Creators Only) -->
    <div v-if="isCreator && activeTab === 'wallet'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-wallet-3-line text-green-600 dark:text-green-400 text-xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Wallet Balance</h2>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Manage your earnings and request payouts
              </p>
            </div>
          </div>
          <button 
            @click="refreshWalletData" 
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
            :class="{ 'animate-spin': refreshing }"
          >
            <i class="ri-refresh-line text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Total Balance -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-700">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-sm"></i>
              </div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Balance</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(walletData.total_balance || 0) }}</p>
          </div>
          
          <!-- Pending Balance -->
          <div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl p-4 border border-orange-200 dark:border-orange-700">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-time-line text-orange-600 dark:text-orange-400 text-sm"></i>
              </div>
              <div class="flex items-center gap-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pending Balance</span>
                <button 
                  @click="showPendingInfo = true" 
                  class="text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300"
                >
                  <i class="ri-question-line text-sm"></i>
                </button>
              </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(walletData.pending_balance || 0) }}</p>
          </div>
          
          <!-- Available for Payout -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-bank-card-line text-blue-600 dark:text-blue-400 text-sm"></i>
              </div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Available for Payout</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatCurrency(walletData.available_for_payout || 0) }}</p>
          </div>
        </div>

        <button 
          @click="showPayoutModal = true" 
          class="w-full py-4 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-xl font-semibold transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="!canRequestPayout"
          :class="{ 'opacity-50 cursor-not-allowed': !canRequestPayout }"
        >
          <div class="flex items-center justify-center gap-2">
            <i class="ri-bank-card-line"></i>
            Request Payout
          </div>
        </button>
      </div>
    </div>

    <!-- Payment Methods Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Credit Cards -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-bank-card-line text-blue-600 dark:text-blue-400 text-xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Visa, Mastercard & Discover</h2>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Add and manage your credit cards
              </p>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <button 
            @click="isAddCardModalOpen = true"
            class="w-full flex flex-col items-center justify-center p-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border-2 border-dashed border-blue-200 dark:border-blue-700 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all duration-200 group"
          >
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-200">
              <i class="ri-add-line text-blue-600 dark:text-blue-400 text-2xl"></i>
            </div>
            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-2">Add Card</span>
            <span class="text-sm text-gray-600 dark:text-gray-400 text-center">
              Add a new credit or debit card to your account
            </span>
          </button>
        </div>
      </div>

      <!-- Alternative Payment Methods -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-bank-line text-green-600 dark:text-green-400 text-xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Alternative Payment Methods</h2>
              <p class="text-sm text-gray-600 dark:text-gray-300">
                Connect bank accounts and other payment methods
              </p>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <button class="w-full flex flex-col items-center justify-center p-8 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-2 border-dashed border-green-200 dark:border-green-700 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 transition-all duration-200 group">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-200">
              <i class="ri-bank-line text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
            <span class="text-lg font-semibold text-green-600 dark:text-green-400 mb-2">Add Alternative Method</span>
            <span class="text-sm text-gray-600 dark:text-gray-400 text-center">
              Connect bank accounts, PayPal, or other payment methods
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Payout Methods Section (Creators Only) -->
    <div v-if="isCreator && activeTab === 'wallet'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-bank-card-line text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Payout Methods</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Manage how you receive your earnings
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Existing Payout Methods -->
        <div v-if="payoutMethods.length > 0" class="space-y-3 mb-6">
          <div 
            v-for="method in payoutMethods" 
            :key="method.id"
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-bank-card-line text-purple-600 dark:text-purple-400 text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ method.provider }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">ending in {{ method.account_number.slice(-4) }}</p>
              </div>
            </div>
            <button 
              @click="deletePayoutMethod(method.id)" 
              class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
            >
              <i class="ri-delete-bin-line text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Add Payout Method Button -->
        <button 
          @click="showAddPayoutMethodModal = true"
          class="w-full flex flex-col items-center justify-center p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-2 border-dashed border-purple-200 dark:border-purple-700 hover:border-purple-300 dark:hover:border-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition-all duration-200 group"
        >
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
            <i class="ri-add-line text-purple-600 dark:text-purple-400 text-xl"></i>
          </div>
          <span class="text-lg font-semibold text-purple-600 dark:text-purple-400">Add Payout Method</span>
        </button>
      </div>
    </div>

    <!-- Payout Requests (Creators Only) -->
    <div v-if="isCreator && activeTab === 'wallet'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-file-list-3-line text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Payout Requests</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Track your payout request history
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div v-if="payoutRequests.length === 0" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-file-list-3-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Payout Requests</h3>
          <p class="text-gray-500 dark:text-gray-400">Your payout request history will appear here</p>
        </div>
        
        <div v-else class="space-y-3">
          <div 
            v-for="request in payoutRequests" 
            :key="request.id" 
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-lg"></i>
              </div>
              <div class="space-y-1">
                <div class="font-semibold text-gray-900 dark:text-white">
                  Payout #{{ request.reference_id }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(request.created_at) }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-lg font-bold text-green-600 dark:text-green-400">${{ formatCurrency(request.amount) }}</span>
              <span class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="getStatusClass(request.status)">
                {{ formatStatus(request.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Billing History -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-file-list-3-line text-indigo-600 dark:text-indigo-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Billing History</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              View your transaction and billing history
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- History Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
          <nav class="flex gap-8">
            <button
              v-for="historyTab in historyTabs"
              :key="historyTab.id"
              @click="activeHistoryTab = historyTab.id"
              class="pb-4 relative font-medium transition-colors duration-200"
              :class="[
                activeHistoryTab === historyTab.id 
                  ? 'text-indigo-600 dark:text-indigo-400' 
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
              ]"
            >
              {{ historyTab.label }}
              <div 
                v-if="activeHistoryTab === historyTab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"
              ></div>
            </button>
          </nav>
        </div>

        <!-- History Content -->
        <div v-if="loadingHistory" class="text-center py-12">
          <div class="w-8 h-8 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-500 dark:text-gray-400">Loading history...</p>
        </div>

        <div v-else-if="filteredHistory.length === 0" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-file-list-3-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No History Found</h3>
          <p class="text-gray-500 dark:text-gray-400">Your billing history will appear here</p>
        </div>

        <div v-else class="space-y-3">
          <div 
            v-for="item in filteredHistory" 
            :key="item.id" 
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-money-dollar-circle-line text-indigo-600 dark:text-indigo-400 text-lg"></i>
              </div>
              <div class="space-y-1">
                <div class="font-semibold text-gray-900 dark:text-white">{{ item.description }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(item.created_at) }}</div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-lg font-bold"
                    :class="item.amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                {{ item.amount > 0 ? '+' : '' }}${{ formatCurrency(Math.abs(item.amount)) }}
              </span>
              <span class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="getHistoryStatusClass(item.status)">
                {{ formatHistoryStatus(item.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals and other components would go here -->
  <!-- Add Card Modal, Payout Modal, etc. -->
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { useAuthStore } from '@/stores/authStore'
import { useEarningsStore } from '@/stores/earningsStore'

const authStore = useAuthStore()
const earningsStore = useEarningsStore()

// User role check
const isCreator = computed(() => authStore.user?.role === 'creator')

// Tab states
const activeTab = ref('wallet')
const activeHistoryTab = ref('transactions')

// Modal states
const isAddCardModalOpen = ref(false)
const showPayoutModal = ref(false)
const showAddPayoutMethodModal = ref(false)
const showPendingInfo = ref(false)

// Form states
const payoutAmount = ref('')
const selectedPayoutMethodId = ref('')
const processingPayout = ref(false)
const processingPayoutMethod = ref(false)
const refreshing = ref(false)

// Card form
const currentYear = new Date().getFullYear()
const cardForm = ref({
  firstName: '',
  lastName: '',
  address: '',
  city: '',
  country: 'US',
  state: '',
  zipCode: '',
  cardNumber: '',
  cvv: '',
  expMonth: '',
  expYear: ''
})

// Payout method form
const newPayoutMethod = ref({
  type: 'bank',
  provider: '',
  account_number: '',
  account_name: '',
  is_default: false
})

// Data
const walletData = ref({
  total_balance: 0,
  pending_balance: 0,
  available_for_payout: 0
})
const payoutMethods = ref([])
const payoutRequests = ref([])

// Tabs
const tabs = [
  {
    id: 'wallet',
    label: 'Wallet',
    icon: 'ri-wallet-3-line'
  },
  {
    id: 'statistics',
    label: 'Statistics',
    icon: 'ri-bar-chart-line'
  }
]

// History tabs
const historyTabs = [
  {
    id: 'transactions',
    label: 'Transaction History'
  },
  {
    id: 'wallet',
    label: 'Wallet Transaction History'
  }
]

// Sample transactions (from PaymentsSettingsView)
const transactions = [
  {
    id: '739869888088125440',
    date: 'Sunday, Jan 26, 2025 at 3:04 PM',
    type: 'Referral Code Earnings',
    amount: '0.08'
  },
  {
    id: '739378299593688384',
    date: 'Saturday, Jan 25, 2025 at 2:21 AM',
    type: 'Referral Code Earnings',
    amount: '0.12'
  }
]

// Sample payout requests (from PayoutView)
const samplePayoutRequests = [
  {
    id: '734516714004834305',
    date: 'Sunday, Jan 12, 2025 at 3:08 AM',
    amount: '34.53',
    status: 'Processed'
  },
  {
    id: '732716992686993408',
    date: 'Monday, Jan 6, 2025 at 9:21 PM',
    amount: '74',
    status: 'Processed'
  }
]

const states = [
  { code: 'AL', name: 'Alabama' },
  { code: 'AK', name: 'Alaska' },
  { code: 'AZ', name: 'Arizona' },
  { code: 'AR', name: 'Arkansas' },
  { code: 'CA', name: 'California' },
  { code: 'CO', name: 'Colorado' },
  { code: 'CT', name: 'Connecticut' },
  { code: 'DE', name: 'Delaware' },
  { code: 'FL', name: 'Florida' },
  { code: 'GA', name: 'Georgia' },
  { code: 'HI', name: 'Hawaii' },
  { code: 'ID', name: 'Idaho' },
  { code: 'IL', name: 'Illinois' },
  { code: 'IN', name: 'Indiana' },
  { code: 'IA', name: 'Iowa' },
  { code: 'KS', name: 'Kansas' },
  { code: 'KY', name: 'Kentucky' },
  { code: 'LA', name: 'Louisiana' },
  { code: 'ME', name: 'Maine' },
  { code: 'MD', name: 'Maryland' },
  { code: 'MA', name: 'Massachusetts' },
  { code: 'MI', name: 'Michigan' },
  { code: 'MN', name: 'Minnesota' },
  { code: 'MS', name: 'Mississippi' },
  { code: 'MO', name: 'Missouri' },
  { code: 'MT', name: 'Montana' },
  { code: 'NE', name: 'Nebraska' },
  { code: 'NV', name: 'Nevada' },
  { code: 'NH', name: 'New Hampshire' },
  { code: 'NJ', name: 'New Jersey' },
  { code: 'NM', name: 'New Mexico' },
  { code: 'NY', name: 'New York' },
  { code: 'NC', name: 'North Carolina' },
  { code: 'ND', name: 'North Dakota' },
  { code: 'OH', name: 'Ohio' },
  { code: 'OK', name: 'Oklahoma' },
  { code: 'OR', name: 'Oregon' },
  { code: 'PA', name: 'Pennsylvania' },
  { code: 'RI', name: 'Rhode Island' },
  { code: 'SC', name: 'South Carolina' },
  { code: 'SD', name: 'South Dakota' },
  { code: 'TN', name: 'Tennessee' },
  { code: 'TX', name: 'Texas' },
  { code: 'UT', name: 'Utah' },
  { code: 'VT', name: 'Vermont' },
  { code: 'VA', name: 'Virginia' },
  { code: 'WA', name: 'Washington' },
  { code: 'WV', name: 'West Virginia' },
  { code: 'WI', name: 'Wisconsin' },
  { code: 'WY', name: 'Wyoming' }
]

// Computed properties
const canRequestPayout = computed(() => {
  return walletData.value.available_for_payout > 0 && payoutMethods.value.length > 0
})

const isPayoutFormValid = computed(() => {
  const amount = parseFloat(payoutAmount.value)
  return amount > 0 && amount <= walletData.value.available_for_payout && selectedPayoutMethodId.value
})

const isPayoutMethodFormValid = computed(() => {
  return newPayoutMethod.value.provider && newPayoutMethod.value.account_number
})

// Methods
const formatCurrency = (value) => parseFloat(value || 0).toFixed(2)
const formatDate = (dateString) => new Date(dateString).toLocaleString()
const formatStatus = (status) => status.charAt(0).toUpperCase() + status.slice(1)

// Helper functions for the new template
const getStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'processed':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
    case 'pending':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200'
    case 'failed':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
    default:
      return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
  }
}

const getHistoryStatusClass = (status) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
    case 'pending':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200'
    case 'failed':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
    default:
      return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
  }
}

const formatHistoryStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

// Mock data for the new template
const loadingHistory = ref(false)
const filteredHistory = computed(() => {
  // Mock history data - replace with actual data from your store
  return [
    {
      id: '1',
      description: 'Subscription Purchase',
      created_at: new Date().toISOString(),
      amount: 9.99,
      status: 'completed'
    },
    {
      id: '2',
      description: 'Tip Payment',
      created_at: new Date(Date.now() - 86400000).toISOString(),
      amount: 5.00,
      status: 'completed'
    },
    {
      id: '3',
      description: 'Media Purchase',
      created_at: new Date(Date.now() - 172800000).toISOString(),
      amount: 2.99,
      status: 'pending'
    }
  ]
})

const closeAddCardModal = () => {
  isAddCardModalOpen.value = false
  cardForm.value = {
    firstName: '',
    lastName: '',
    address: '',
    city: '',
    country: 'US',
    state: '',
    zipCode: '',
    cardNumber: '',
    cvv: '',
    expMonth: '',
    expYear: ''
  }
}

const handleAddCard = () => {
  console.log('Card form submitted:', cardForm.value)
  closeAddCardModal()
}

const refreshWalletData = async () => {
  if (!isCreator.value) return
  
  refreshing.value = true
  try {
    await earningsStore.loadWalletDashboard()
    // Update local data
    walletData.value = earningsStore.walletData || {
      total_balance: 0,
      pending_balance: 0,
      available_for_payout: 0
    }
    payoutMethods.value = earningsStore.payoutMethods || []
    payoutRequests.value = earningsStore.payoutRequests || []
  } catch (error) {
    console.error('Error refreshing wallet data:', error)
  } finally {
    refreshing.value = false
  }
}

const requestPayout = async () => {
  processingPayout.value = true
  try {
    await earningsStore.requestPayout({
      amount: payoutAmount.value,
      payout_method_id: selectedPayoutMethodId.value
    })
    payoutAmount.value = ''
    selectedPayoutMethodId.value = ''
    showPayoutModal.value = false
    await refreshWalletData()
  } catch (error) {
    console.error('Error requesting payout:', error)
  } finally {
    processingPayout.value = false
  }
}

const addPayoutMethod = async () => {
  processingPayoutMethod.value = true
  try {
    await earningsStore.addPayoutMethod(newPayoutMethod.value)
    newPayoutMethod.value = {
      type: 'bank',
      provider: '',
      account_number: '',
      account_name: '',
      is_default: false
    }
    showAddPayoutMethodModal.value = false
    await refreshWalletData()
  } catch (error) {
    console.error('Error adding payout method:', error)
  } finally {
    processingPayoutMethod.value = false
  }
}

const deletePayoutMethod = async (methodId) => {
  if (!confirm('Are you sure you want to delete this payout method?')) return
  try {
    await earningsStore.deletePayoutMethod(methodId)
    await refreshWalletData()
  } catch (error) {
    console.error('Error deleting payout method:', error)
  }
}

// Load data on mount
onMounted(async () => {
  if (isCreator.value) {
    await refreshWalletData()
  }
})
</script> 