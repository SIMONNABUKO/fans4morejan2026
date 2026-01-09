<template>
  <div class="bg-white dark:bg-gray-900 rounded-lg overflow-hidden shadow-md dark:shadow-gray-800">
    <!-- Banner with overlapping profile picture -->
    <div class="relative">
      <!-- Banner image -->
      <div class="relative h-24 sm:h-32 overflow-hidden">
        <img 
          :src="subscription.creator.avatar" 
          :alt="subscription.creator.name"
          class="w-full h-full object-cover"
        />
        <div class="absolute top-2 right-2 flex gap-2">
          <!-- Dropdown menu for actions -->
          <div class="relative dropdown">
            <button 
              @click="toggleDropdown"
              class="p-1.5 sm:p-2 bg-gray-100/50 dark:bg-gray-900/50 rounded-full hover:bg-gray-200/50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <i class="ri-settings-4-line text-base sm:text-lg text-gray-700 dark:text-gray-300"></i>
            </button>
            
            <!-- Dropdown content -->
            <div 
              v-if="showDropdown" 
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10"
            >
              <div class="py-1">
                <button 
                  v-if="subscription.status === 'expired'"
                  @click="$emit('renew', subscription.id); toggleDropdown()"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <i class="ri-refresh-line mr-2"></i> {{ t('renew') }} {{ t('subscription') }}
                </button>
                <button 
                  v-else-if="subscription.status === 'completed'"
                  @click="$emit('cancel', subscription.id); toggleDropdown()"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <i class="ri-close-circle-line mr-2"></i> {{ t('cancel') }} {{ t('subscription') }}
                </button>
                <button 
                  v-if="!subscription.creator.blocked"
                  @click="$emit('toggle-block', subscription.creator.id, false); toggleDropdown()"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <i class="ri-forbid-line mr-2"></i> {{ t('block') }} {{ t('creator') }}
                </button>
                <button 
                  v-else
                  @click="$emit('toggle-block', subscription.creator.id, true); toggleDropdown()"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <i class="ri-user-unfollow-line mr-2"></i> {{ t('unfollow') }} {{ t('creator') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Profile picture -->
      <div class="absolute -bottom-8 sm:-bottom-10 left-3 sm:left-4">
        <img 
          :src="subscription.creator.avatar" 
          :alt="subscription.creator.name"
          class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-4 border-white dark:border-gray-900 object-cover"
        />
      </div>
    </div>

    <!-- Content - adjusted padding to accommodate overlapping profile picture -->
    <div class="pt-10 sm:pt-12 p-3 sm:p-4 space-y-2">
      <!-- Creator info -->
      <div class="flex items-center gap-2">
        <span class="font-semibold text-sm sm:text-base">{{ subscription.creator.name }}</span>
        <i v-if="subscription.creator.verified" class="ri-verified-badge-fill text-blue-600 dark:text-blue-500 text-sm sm:text-base"></i>
        <i v-if="subscription.creator.blocked" class="ri-forbid-line text-red-600 dark:text-red-500 text-sm sm:text-base"></i>
      </div>
      <div class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm">{{ subscription.creator.username }}</div>

      <!-- Subscription details -->
      <div class="space-y-1 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
        <div class="flex items-center gap-2">
          <i class="ri-vip-crown-line w-4 sm:w-5" :style="tierColorStyle"></i>
          <span>Subscription Tier: {{ subscription.tier }}</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="ri-timer-line w-4 sm:w-5 text-blue-600 dark:text-blue-500"></i>
          <span>Status: {{ capitalizeFirstLetter(subscription.status) }}</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="ri-calendar-line w-4 sm:w-5 text-green-600 dark:text-green-500"></i>
          <span>Billing Cycle: {{ subscription.billingCycle }}</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="ri-time-line w-4 sm:w-5 text-purple-600 dark:text-purple-500"></i>
          <span>Total Months Subscribed: {{ subscription.totalMonths }} {{ subscription.totalMonths === 1 ? 'Month' : 'Months' }}</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="ri-money-dollar-circle-line w-4 sm:w-5 text-green-600 dark:text-green-500"></i>
          <span>Subscription Price: ${{ subscription.price }}</span>
        </div>
        <div class="flex items-center gap-2">
          <i class="ri-calendar-check-line w-4 sm:w-5 text-red-600 dark:text-red-500"></i>
          <span>{{ subscription.status === 'expired' ? 'Sub Expired Date:' : 'Sub Expiry Date:' }} {{ subscription.expiredDate }}</span>
        </div>
        
        <!-- Tier Benefits (if available) -->
        <div v-if="subscription.tierBenefits && subscription.tierBenefits.length > 0" class="mt-3">
          <div class="font-medium mb-1">Tier Benefits:</div>
          <ul class="list-disc list-inside pl-1 space-y-1">
            <li v-for="(benefit, index) in subscription.tierBenefits" :key="index">
              {{ benefit }}
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Action buttons for mobile - visible on smaller screens -->
      <div class="flex gap-2 pt-2 sm:hidden">
        <button 
          v-if="subscription.status === 'expired'"
          @click="$emit('renew', subscription.id)"
          class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs"
        >
          <i class="ri-refresh-line mr-1"></i> {{ t('renew') }}
        </button>
        <button 
          v-else-if="subscription.status === 'completed'"
          @click="$emit('cancel', subscription.id)"
          class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs"
        >
          <i class="ri-close-circle-line mr-1"></i> {{ t('cancel') }}
        </button>
        <button 
          v-if="!subscription.creator.blocked"
          @click="$emit('toggle-block', subscription.creator.id, false)"
          class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-xs"
        >
          <i class="ri-forbid-line mr-1"></i> {{ t('block') }}
        </button>
        <button 
          v-else
          @click="$emit('toggle-block', subscription.creator.id, true)"
          class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-xs"
        >
          <i class="ri-user-unfollow-line mr-1"></i> {{ t('unfollow') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  subscription: {
    type: Object,
    required: true
  }
})

defineEmits(['renew', 'cancel', 'toggle-block'])

const showDropdown = ref(false)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

// Helper function to capitalize the first letter
const capitalizeFirstLetter = (string) => {
  return string.charAt(0).toUpperCase() + string.slice(1)
}

// Compute tier color style
const tierColorStyle = computed(() => {
  if (props.subscription.tierColor) {
    return { color: props.subscription.tierColor }
  }
  return { color: '#FFD700' } // Default gold color for crown icon
})

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (showDropdown.value && !event.target.closest('.dropdown')) {
    showDropdown.value = false
  }
}

// Add event listener when component is mounted
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

// Remove event listener when component is unmounted
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const { t } = useI18n()
</script>

