<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/settings" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ t('payments') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your payment methods and billing
            </p>
          </div>
        </div>
        
        <!-- Right Side: Payment Status -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Payment Methods</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">2 Active</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Payment Methods Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Credit Cards Section -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <i class="ri-bank-card-line text-blue-600 dark:text-blue-400 text-xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('visa_mastercard_discover') }}</h2>
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
            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-2">{{ t('add_card') }}</span>
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
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('alternative_payment_methods') }}</h2>
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
            <span class="text-lg font-semibold text-green-600 dark:text-green-400 mb-2">{{ t('add_alternative_method') }}</span>
            <span class="text-sm text-gray-600 dark:text-gray-400 text-center">
              Connect bank accounts, PayPal, or other payment methods
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Billing History -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-file-list-3-line text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('billing_history') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              View your transaction history and billing records
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
          <nav class="flex gap-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="pb-4 relative font-medium transition-colors duration-200"
              :class="[
                activeTab === tab.id 
                  ? 'text-purple-600 dark:text-purple-400' 
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
              ]"
            >
              {{ tab.label }}
              <div 
                v-if="activeTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400 rounded-full"
              ></div>
            </button>
          </nav>
        </div>

        <!-- Transactions List -->
        <div class="space-y-4">
          <div 
            v-for="transaction in transactions" 
            :key="transaction.id"
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-lg"></i>
              </div>
              <div class="space-y-1">
                <div class="font-semibold text-gray-900 dark:text-white">
                  {{ transaction.type }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ transaction.date }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-500">
                  {{ t('transaction') }} #{{ transaction.id }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-lg font-bold text-green-600 dark:text-green-400">+${{ transaction.amount }}</span>
              <button class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="transactions.length === 0" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-file-list-3-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Transactions Yet</h3>
          <p class="text-gray-500 dark:text-gray-400">Your transaction history will appear here</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Card Modal -->
  <TransitionRoot appear :show="isAddCardModalOpen" as="template">
    <Dialog as="div" @close="closeAddCardModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-2xl transition-all border border-gray-100 dark:border-gray-700">
              <div class="flex justify-between items-center mb-6">
                <DialogTitle as="h3" class="text-xl font-semibold text-gray-900 dark:text-white">
                  {{ t('add_a_new_payment_method') }}
                </DialogTitle>
                <button 
                  @click="closeAddCardModal"
                  class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </div>

              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="ri-shield-check-line text-blue-600 dark:text-blue-400 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">Secure Payment Processing</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                      {{ t('select_media_fans4more_is_fully_compliant_with_payment_card_industry_data_security_standards') }}
                    </p>
                  </div>
                </div>
              </div>

              <form @submit.prevent="handleAddCard" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                    <input
                      type="text"
                      v-model="cardForm.firstName"
                      placeholder="First/Given Name"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                    <input
                      type="text"
                      v-model="cardForm.lastName"
                      placeholder="Last Name"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                  <input
                    type="text"
                    v-model="cardForm.address"
                    placeholder="Address"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City</label>
                  <input
                    type="text"
                    v-model="cardForm.city"
                    placeholder="City"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
                    <select
                      v-model="cardForm.country"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    >
                      <option value="US">United States of America (the)</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">State</label>
                    <select
                      v-model="cardForm.state"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    >
                      <option value="">State / Province</option>
                      <option v-for="state in states" :key="state.code" :value="state.code">
                        {{ state.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zip Code</label>
                  <input
                    type="text"
                    v-model="cardForm.zipCode"
                    placeholder="Zip Code"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Card Number</label>
                  <input
                    type="text"
                    v-model="cardForm.cardNumber"
                    placeholder="Card Number"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                  />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CVV/CVC</label>
                    <input
                      type="text"
                      v-model="cardForm.cvv"
                      placeholder="CVV/CVC"
                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    />
                  </div>

                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Month</label>
                      <select
                        v-model="cardForm.expMonth"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      >
                        <option value="">MM</option>
                        <option v-for="month in 12" :key="month" :value="month">
                          {{ month.toString().padStart(2, '0') }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Year</label>
                      <select
                        v-model="cardForm.expYear"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      >
                        <option value="">YYYY</option>
                        <option v-for="year in 10" :key="year" :value="currentYear + year">
                          {{ currentYear + year }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                  <div class="flex gap-3">
                    <img src="/mc.svg" alt="Mastercard" class="h-8" />
                    <img src="/visa.png" alt="Visa" class="h-8" />
                    <img src="/discover.png" alt="Discover" class="h-8" />
                  </div>
                  <button
                    type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white rounded-xl font-semibold transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    {{ t('add_card') }}
                  </button>
                </div>
              </form>

              <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                <div class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="ri-information-line text-yellow-600 dark:text-yellow-400 text-sm"></i>
                  </div>
                  <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    {{ t('fans4more_will_make_a_one_time_charge_up_to_010_to_verify_the_card') }}
                  </p>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</div>
</template>

<script setup>
import { ref } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const isAddCardModalOpen = ref(false)
const activeTab = ref('transactions')

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

const tabs = [
  {
    id: 'transactions',
    label: 'Transaction History'
  },
  {
    id: 'wallet',
    label: 'Wallet Transaction History'
  }
]

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
  // Handle card submission
  console.log('Card form submitted:', cardForm.value)
  closeAddCardModal()
}
</script>