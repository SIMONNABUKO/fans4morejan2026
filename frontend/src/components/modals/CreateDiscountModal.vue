<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" @close="onClose">
      <div class="relative w-full max-w-lg mx-auto bg-background-light dark:bg-background-dark rounded-lg shadow-lg flex flex-col h-full md:h-auto md:my-16">
        <button @click="onClose" class="absolute top-4 right-4 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark focus:outline-none">
          <span class="sr-only">Close</span>
          <i class="ri-close-line text-2xl"></i>
        </button>
        <div class="flex-1 flex flex-col p-0">
          <!-- Header -->
          <div class="px-6 pt-6 pb-2 border-b border-border-light dark:border-border-dark">
            <h2 class="text-xl font-bold text-left text-text-light-primary dark:text-text-dark-primary">Create a new Discount</h2>
          </div>
          <!-- Form -->
          <div class="px-6 py-4 overflow-y-auto" style="max-height:70vh;">
            <form class="space-y-6" @submit.prevent="handleSubmit">
              <div>
                <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Package</label>
                <select v-model="form.tier_id" :disabled="activeTiers.length === 0" @change="onTierChange" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2">
                  <option value="" disabled>Select a package</option>
                  <option v-for="tier in activeTiers" :key="tier.id" :value="tier.id">{{ tier.title }}</option>
                </select>
                <p v-if="activeTiers.length === 0" class="text-accent-danger text-xs mt-2">You have no active subscription packages. Please create a subscription package before creating a discount.</p>
                <p v-if="errors.tier_id" class="text-accent-danger text-xs mt-1">{{ errors.tier_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Label</label>
                <input v-model="form.label" type="text" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="Enter discount label" />
                <p v-if="errors.label" class="text-accent-danger text-xs mt-1">{{ errors.label }}</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Maximum Uses</label>
                  <input v-model.number="form.max_uses" type="number" min="1" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="100" />
                  <p v-if="errors.max_uses" class="text-accent-danger text-xs mt-1">{{ errors.max_uses }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Discount Percent (%)</label>
                  <input v-model.number="form.discount_percent" type="number" min="1" max="100" @input="calculateDiscountedPrice" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="e.g. 10" />
                  <p v-if="errors.discount_percent" class="text-accent-danger text-xs mt-1">{{ errors.discount_percent }}</p>
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Discounted Price ($)</label>
                  <input v-model.number="form.discounted_price" type="number" min="0" step="0.01" @input="calculateDiscountPercent" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="e.g. 5.00" />
                  <p v-if="errors.discounted_price" class="text-accent-danger text-xs mt-1">{{ errors.discounted_price }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Amount Off ($)</label>
                  <input v-model.number="form.amount_off" type="number" min="0" step="0.01" @input="calculateFromAmountOff" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="e.g. 2.00" />
                  <p v-if="errors.amount_off" class="text-accent-danger text-xs mt-1">{{ errors.amount_off }}</p>
                </div>
              </div>

              <!-- Price Breakdown -->
              <div v-if="selectedTier && (form.discount_percent > 0 || form.discounted_price > 0)" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Price Breakdown</h4>
                <div class="space-y-1 text-sm text-blue-800 dark:text-blue-200">
                  <div>Original Price: ${{ selectedTier.base_price }}</div>
                  <div v-if="form.discount_percent > 0">Discount: {{ form.discount_percent }}%</div>
                  <div v-if="form.amount_off > 0">Amount Off: ${{ form.amount_off }}</div>
                  <div class="font-semibold">Final Price: ${{ form.discounted_price || (selectedTier.base_price - form.amount_off) }}</div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Start Date</label>
                  <input v-model="form.start_date" type="date" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" />
                  <p v-if="errors.start_date" class="text-accent-danger text-xs mt-1">{{ errors.start_date }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">End Date</label>
                  <input v-model="form.end_date" type="date" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" />
                  <p v-if="errors.end_date" class="text-accent-danger text-xs mt-1">{{ errors.end_date }}</p>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary mb-1">Duration until Rebill (days)</label>
                <input v-model.number="form.duration_days" type="number" min="1" class="w-full p-2 border border-border-light dark:border-border-dark rounded focus:outline-none focus:ring bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary mt-2" placeholder="e.g. 30" />
                <p v-if="errors.duration_days" class="text-accent-danger text-xs mt-1">{{ errors.duration_days }}</p>
              </div>
              <div class="flex items-center">
                <input v-model="form.exclude_previous_claimers" type="checkbox" id="exclude_previous_claimers" class="mr-2" />
                <label for="exclude_previous_claimers" class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Exclude users who have claimed a discount for this package before</label>
              </div>
              <div v-if="apiError" class="text-accent-danger text-sm">{{ apiError }}</div>
              <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="onClose" class="px-4 py-2 rounded-lg bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light/80 dark:hover:bg-surface-dark/80 border border-border-light dark:border-border-dark">Cancel</button>
                <button type="submit" :disabled="loading" class="px-4 py-2 rounded-lg bg-primary-light dark:bg-primary-dark text-white hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover disabled:opacity-50">
                  <span v-if="loading"><i class="ri-loader-4-line animate-spin mr-1"></i> Creating...</span>
                  <span v-else>Create Discount</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { Dialog, DialogOverlay, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import axios from '@/axios'

const props = defineProps({
  open: Boolean,
  onClose: Function,
})

const emit = defineEmits(['created'])

const form = ref({
  tier_id: '',
  label: '',
  max_uses: 100,
  discounted_price: '',
  discount_percent: '',
  amount_off: '',
  start_date: '',
  end_date: '',
  duration_days: '',
  exclude_previous_claimers: false,
})

const errors = ref({})
const apiError = ref('')
const loading = ref(false)
const activeTiers = ref([])

// Computed property to get the selected tier
const selectedTier = computed(() => {
  return activeTiers.value.find(tier => tier.id == form.value.tier_id)
})

const fetchActiveTiers = async () => {
  try {
    const res = await axios.get('tiers?status=active')
    activeTiers.value = res.data.data || []
  } catch (e) {
    activeTiers.value = []
  }
}

onMounted(fetchActiveTiers)

watch(() => props.open, (val) => {
  if (val) fetchActiveTiers()
})

// Function to calculate discounted price from percentage
const calculateDiscountedPrice = () => {
  if (!selectedTier.value || !form.value.discount_percent) {
    form.value.discounted_price = ''
    form.value.amount_off = ''
    return
  }
  
  const originalPrice = selectedTier.value.base_price
  const discountPercent = form.value.discount_percent / 100
  const discountedPrice = originalPrice * (1 - discountPercent)
  const amountOff = originalPrice - discountedPrice
  
  form.value.discounted_price = Math.round(discountedPrice * 100) / 100
  form.value.amount_off = Math.round(amountOff * 100) / 100
}

// Function to calculate discount percentage from discounted price
const calculateDiscountPercent = () => {
  if (!selectedTier.value || !form.value.discounted_price) {
    form.value.discount_percent = ''
    form.value.amount_off = ''
    return
  }
  
  const originalPrice = selectedTier.value.base_price
  const discountedPrice = form.value.discounted_price
  const discountPercent = ((originalPrice - discountedPrice) / originalPrice) * 100
  const amountOff = originalPrice - discountedPrice
  
  form.value.discount_percent = Math.round(discountPercent)
  form.value.amount_off = Math.round(amountOff * 100) / 100
}

// Function to calculate from amount off
const calculateFromAmountOff = () => {
  if (!selectedTier.value || !form.value.amount_off) {
    form.value.discount_percent = ''
    form.value.discounted_price = ''
    return
  }
  
  const originalPrice = selectedTier.value.base_price
  const amountOff = form.value.amount_off
  const discountedPrice = originalPrice - amountOff
  const discountPercent = (amountOff / originalPrice) * 100
  
  form.value.discounted_price = Math.round(discountedPrice * 100) / 100
  form.value.discount_percent = Math.round(discountPercent)
}

// Function to handle tier change
const onTierChange = () => {
  // Reset discount calculations when tier changes
  form.value.discounted_price = ''
  form.value.discount_percent = ''
  form.value.amount_off = ''
}

const handleSubmit = async () => {
  errors.value = {}
  apiError.value = ''
  loading.value = true
  try {
    const res = await axios.post(`/api/tiers/${form.value.tier_id}/discounts`, form.value)
    emit('created', res.data.data)
    onClose()
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
      errors.value = e.response.data.errors
    } else if (e.response && e.response.data && e.response.data.message) {
      apiError.value = e.response.data.message
    } else {
      apiError.value = 'An error occurred.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #888 #222;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #222;
}
</style> 