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
              {{ t('plans_promos') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your subscription tiers and promotional offers
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Stats -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Active Tiers</div>
            <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ enabledTiers.length }}</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="space-y-8">
      <!-- Active Subscription Packages -->
      <section class="space-y-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-vip-crown-line text-green-600 dark:text-green-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('active_subscription_tiers') }}</h2>
          </div>
          <button 
            @click="showCreateTierModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-add-line text-sm"></i>
            </div>
            <span class="text-sm">Create Tier</span>
          </button>
        </div>

                  <div v-if="enabledTiers.length === 0" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-vip-crown-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ t('no_active_subscription_tiers') }}</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first subscription tier to start earning</p>
          <button 
            @click="showCreateTierModal = true"
            class="inline-flex items-center gap-2 px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-line"></i>
            Create First Tier
          </button>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div 
            v-for="tier in enabledTiers" 
            :key="tier.id"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 hover:scale-[1.02]"
          >
            <!-- Package Header -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ tier.title }}</h3>
                <div class="flex items-center gap-2">
                  <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                    <i class="ri-arrow-up-s-line"></i>
                  </button>
                  <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                    <i class="ri-arrow-down-s-line"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Subscription Options -->
            <div class="p-6 space-y-3" :style="{ backgroundColor: adjustColor(tier.color_code, -30) }">
              <!-- Base Package -->
              <div 
                class="p-4 rounded-xl flex items-center justify-between transition-all duration-200 hover:scale-[1.02] cursor-pointer"
                :style="{ 
                  backgroundColor: tier.color_code,
                }"
                @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, 10)"
                @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-line text-white text-lg"></i>
                  </div>
                  <div>
                    <div class="text-white font-semibold">{{ tier.title }} {{ t('1_month') }}</div>
                    <div class="text-white/80 text-sm">Standard subscription</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-white text-xl font-bold">${{ tier.base_price }}</span>
                  <span v-if="tier.promotion" class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                    <i class="ri-price-tag-3-line"></i>
                    {{ t('promotion') }}
                  </span>
                </div>
              </div>

              <!-- Additional Packages -->
              <div 
                v-if="tier.two_month_price > 0"
                class="p-4 rounded-xl flex items-center justify-between transition-all duration-200 hover:scale-[1.02] cursor-pointer"
                :style="{ 
                  backgroundColor: tier.color_code,
                }"
                @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, 10)"
                @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-2-line text-white text-lg"></i>
                  </div>
                  <div>
                    <div class="text-white font-semibold">{{ tier.title }} {{ t('2_months') }}</div>
                    <div class="text-white/80 text-sm">Save {{ tier.two_month_discount }}%</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-white text-xl font-bold">${{ tier.two_month_price }}</span>
                  <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ tier.two_month_discount }}% {{ t('off') }}
                  </span>
                </div>
              </div>

              <div 
                v-if="tier.three_month_price > 0"
                class="p-4 rounded-xl flex items-center justify-between transition-all duration-200 hover:scale-[1.02] cursor-pointer"
                :style="{ 
                  backgroundColor: tier.color_code,
                }"
                @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, 10)"
                @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-3-line text-white text-lg"></i>
                  </div>
                  <div>
                    <div class="text-white font-semibold">{{ tier.title }} {{ t('3_months') }}</div>
                    <div class="text-white/80 text-sm">Save {{ tier.three_month_discount }}%</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-white text-xl font-bold">${{ tier.three_month_price }}</span>
                  <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ tier.three_month_discount }}% {{ t('off') }}
                  </span>
                </div>
              </div>

              <div 
                v-if="tier.six_month_price > 0"
                class="p-4 rounded-xl flex items-center justify-between transition-all duration-200 hover:scale-[1.02] cursor-pointer"
                :style="{ 
                  backgroundColor: tier.color_code,
                }"
                @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, 10)"
                @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-4-line text-white text-lg"></i>
                  </div>
                  <div>
                    <div class="text-white font-semibold">{{ tier.title }} {{ t('6_months') }}</div>
                    <div class="text-white/80 text-sm">Save {{ tier.six_month_discount }}%</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-white text-xl font-bold">${{ tier.six_month_price }}</span>
                  <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ tier.six_month_discount }}% {{ t('off') }}
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3 pt-4">
                <button 
                  @click="openEditModal(tier)"
                  class="flex-1 px-4 py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-semibold transition-all duration-200 hover:scale-105"
                  :style="{
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    color: '#3b82f6',
                    borderColor: 'rgba(59, 130, 246, 0.2)',
                  }"
                >
                  <i class="ri-edit-line"></i>
                  {{ t('edit_subscription_tier') }}
                </button>
                <button 
                  @click="toggleTierStatus(tier.id)"
                  class="px-4 py-3 rounded-xl flex items-center justify-center gap-2 text-sm font-semibold bg-black/30 text-white hover:bg-black/40 transition-all duration-200 hover:scale-105"
                >
                  <i class="ri-eye-off-line"></i>
                  {{ t('disable') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Disabled Subscription Packages -->
              <section v-if="disabledTiers.length > 0" class="space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
            <i class="ri-eye-off-line text-gray-600 dark:text-gray-400 text-lg"></i>
          </div>
          <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('disabled_subscription_tiers') }}</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div 
            v-for="tier in disabledTiers" 
            :key="tier.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                  <i class="ri-vip-crown-line text-gray-400 dark:text-gray-500 text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ tier.title }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Currently disabled</p>
                </div>
              </div>
              <div class="flex gap-2">
                <button 
                  @click="toggleTierStatus(Number(tier.id))" 
                  class="px-3 py-2 rounded-lg flex items-center gap-2 text-sm font-medium bg-green-500/10 text-green-600 dark:text-green-400 hover:bg-green-500/20 transition-all duration-200 hover:scale-105"
                >
                  <i class="ri-eye-line"></i>
                  {{ t('enable') }}
                </button>
                <button 
                  @click="deleteTier(Number(tier.id))" 
                  class="px-3 py-2 rounded-lg flex items-center gap-2 text-sm font-medium bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition-all duration-200 hover:scale-105"
                >
                  <i class="ri-delete-bin-line"></i>
                  {{ t('delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Subscription Discounts -->
      <section class="space-y-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-price-tag-3-line text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">Subscription Discounts</h2>
          </div>
          <button 
            @click="showCreateDiscountModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-600 dark:focus:ring-purple-500"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-add-line text-sm"></i>
            </div>
            <span class="text-sm">Create Discount</span>
          </button>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
              <i class="ri-information-line text-white text-sm"></i>
            </div>
            <div>
              <h3 class="text-blue-900 dark:text-blue-100 font-semibold mb-2">NEW: Enhanced Discount System</h3>
              <p class="text-blue-800 dark:text-blue-200 text-sm leading-relaxed">
                When a user already has an active subscription to the same Package they can still claim your discount without having to wait for their subscription to expire. The remaining days of their current subscription will be added on top of the Discount duration.
              </p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-800">
          <nav class="flex gap-6">
            <button 
              v-for="tab in discountTabs" 
              :key="tab.id"
              @click="activeDiscountTab = tab.id"
              class="pb-3 relative whitespace-nowrap font-medium transition-colors duration-200"
              :class="activeDiscountTab === tab.id ? 'text-purple-600 dark:text-purple-400' : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary'"
            >
              {{ tab.label }}
              <div 
                v-if="activeDiscountTab === tab.id"
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-600 dark:bg-purple-400"
              ></div>
            </button>
          </nav>
        </div>

        <!-- Discount List -->
        <div v-if="filteredDiscounts.length === 0" class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-price-tag-3-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Discounts Yet</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">Create your first discount to attract more subscribers</p>
          <button 
            @click="showCreateDiscountModal = true"
            class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-line"></i>
            Create First Discount
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="discount in filteredDiscounts" 
            :key="discount.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-200 hover:scale-[1.02]"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-price-tag-3-line text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ discount.name }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ discount.discount }}% off</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Modals -->
  <CreateSubscriptionTierModal
    :is-open="showCreateTierModal"
    @close="showCreateTierModal = false"
    @create="handleCreateTier"
  />
  <EditSubscriptionTierModal
    :is-open="showEditTierModal"
    :tier="selectedTier"
    @close="closeEditModal"
    @update="handleUpdateTier"
  />
  <CreateDiscountModal :open="showCreateDiscountModal" :onClose="() => (showCreateDiscountModal = false)" @created="handleDiscountCreated" />
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'
import CreateSubscriptionTierModal from '@/components/subscriptions/CreateSubscriptionTierModal.vue'
import EditSubscriptionTierModal from '@/components/subscriptions/EditSubscriptionTierModal.vue'
import { useI18n } from 'vue-i18n'
import CreateDiscountModal from '@/components/modals/CreateDiscountModal.vue'

const { t } = useI18n()

// Dynamic imports for stores
let subscriptionStore = null
let creatorSettingsStore = null

// Toggle states
const requirePaymentMethod = computed({
  get: () => creatorSettingsStore?.requirePaymentMethod || false,
  set: async () => {
    try {
      if (!creatorSettingsStore) {
        const { useCreatorSettingsStore } = await import('@/stores/creatorSettingsStore')
        creatorSettingsStore = useCreatorSettingsStore()
      }
      await creatorSettingsStore.togglePaymentMethodRequirement()
    } catch (error) {
      console.error('Failed to toggle payment method requirement:', error)
    }
  }
})

// Computed properties for subscription store data
const enabledTiers = computed(() => subscriptionStore?.enabledTiers || [])
const disabledTiers = computed(() => subscriptionStore?.disabledTiers || [])
const allTiers = computed(() => subscriptionStore?.tiers || [])

// Modal states
const showCreateTierModal = ref(false)
const showEditTierModal = ref(false)
const selectedTier = ref(null)
const showCreateDiscountModal = ref(false)

// Discount tabs
const discountTabs = [
  { id: 'active', label: 'Active' },
  { id: 'inactive', label: 'Inactive' }
]
const activeDiscountTab = ref('active')

// Discounts data
const discounts = ref([])

// Computed
const filteredDiscounts = computed(() => {
  return discounts.value.filter(discount => {
    return activeDiscountTab.value === 'active' 
      ? discount.status === 'active'
      : discount.status === 'inactive'
  })
})

// Fetch settings on component mount
onMounted(async () => {
  console.log('Component mounted, fetching settings...')
  try {
    // Load subscription store dynamically
    const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
    subscriptionStore = useSubscriptionStore()
    
    // Load creator settings store dynamically
    const { useCreatorSettingsStore } = await import('@/stores/creatorSettingsStore')
    creatorSettingsStore = useCreatorSettingsStore()

    await Promise.all([
      creatorSettingsStore.fetchSettings(),
      subscriptionStore.fetchTiers()
    ])
  } catch (error) {
    console.error('Error fetching data:', error)
  }
})

// Handlers
const handleCreateTier = async (tierData) => {
  try {
    if (!subscriptionStore) {
      const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
      subscriptionStore = useSubscriptionStore()
    }
    const result = await subscriptionStore.createTier(tierData)
    if (result.success) {
      console.log('New tier created:', result.tier)
      await subscriptionStore.fetchTiers() // Refresh the tiers list
    } else {
      console.error('Failed to create tier:', result.error)
    }
  } catch (error) {
    console.error('An unexpected error occurred while creating tier:', error)
  }
  showCreateTierModal.value = false
}

const openEditModal = (tier) => {
  selectedTier.value = tier
  showEditTierModal.value = true
}

const closeEditModal = () => {
  showEditTierModal.value = false
  selectedTier.value = null
}

const handleUpdateTier = async (updatedTier) => {
  try {
    if (!subscriptionStore) {
      const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
      subscriptionStore = useSubscriptionStore()
    }
    const result = await subscriptionStore.updateTier(updatedTier.id, updatedTier)
    if (result.success) {
      console.log('Tier updated:', result.tier)
      await subscriptionStore.fetchTiers() // Refresh the tiers list
    } else {
      console.error('Failed to update tier:', result.error)
    }
  } catch (error) {
    console.error('An unexpected error occurred while updating tier:', error)
  }
  closeEditModal()
}

const toggleTierStatus = async (tierId) => {
  try {
    if (!tierId || isNaN(Number(tierId))) {
      console.error('Invalid tier ID:', tierId)
      return
    }

    if (!subscriptionStore) {
      const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
      subscriptionStore = useSubscriptionStore()
    }

    const numericTierId = Number(tierId)
    console.log('Attempting to toggle tier status:', {
      tierId: numericTierId,
      tier: subscriptionStore.tiers.find(t => t.id === numericTierId),
      allTiers: subscriptionStore.tiers,
      enabledTiers: subscriptionStore.enabledTiers,
      disabledTiers: subscriptionStore.disabledTiers
    })
    
    const result = await subscriptionStore.toggleTierStatus(numericTierId)
    console.log('Toggle tier status result:', result)
    
    if (result.success) {
      console.log('Tier status updated successfully:', result.tier)
      // Refresh the tiers list to ensure we have the latest state
      await subscriptionStore.fetchTiers()
      console.log('Store state after refresh:', {
        allTiers: subscriptionStore.tiers,
        enabledTiers: subscriptionStore.enabledTiers,
        disabledTiers: subscriptionStore.disabledTiers
      })
    } else {
      console.error('Failed to update tier status:', result.error)
    }
  } catch (error) {
    console.error('An unexpected error occurred while updating tier status:', error)
  }
}

const deleteTier = async (tierId) => {
  if (confirm(`Are you sure you want to delete this tier?`)) {
    try {
      if (!subscriptionStore) {
        const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
        subscriptionStore = useSubscriptionStore()
      }
      const result = await subscriptionStore.deleteTier(tierId)
      if (result.success) {
        console.log('Tier deleted:', tierId)
        await subscriptionStore.fetchTiers() // Refresh the tiers list
      } else {
        console.error('Failed to delete tier:', result.error)
      }
    } catch (error) {
      console.error('An unexpected error occurred while deleting tier:', error)
    }
  }
}

const expandedTiers = ref(new Set());

const toggleTierExpanded = (tierId) => {
  if (expandedTiers.value.has(tierId)) {
    expandedTiers.value.delete(tierId);
  } else {
    expandedTiers.value.add(tierId);
  }
};

const adjustColor = (color, amount) => {
  const hex = color.replace('#', '');
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);

  const newR = Math.max(0, Math.min(255, r + amount));
  const newG = Math.max(0, Math.min(255, g + amount));
  const newB = Math.max(0, Math.min(255, b + amount));

  return `rgb(${newR}, ${newG}, ${newB})`;
};

const fetchDiscounts = async () => {
  // Fetch discounts for all active packages (tiers)
  // This assumes you have a way to get all active tier IDs
  // and an API endpoint to fetch discounts for each
  // For demo, just set to empty array
  discounts.value = []
}

const handleDiscountCreated = (discount) => {
  showCreateDiscountModal.value = false
  fetchDiscounts()
}
</script>

