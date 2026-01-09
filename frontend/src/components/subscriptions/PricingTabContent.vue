<template>
  <div class="space-y-4">
    <!-- Base Price -->
    <div class="space-y-2">
      <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
        Base Price (1 Month)
      </label>
      <div class="relative flex-1">
        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-sm text-text-light-secondary dark:text-text-dark-secondary">$</span>
        <input
          v-model.number="basePrice"
          type="number"
          min="0"
          step="0.01"
          placeholder="Amount"
          class="w-full pl-6 pr-2 py-1.5 text-sm bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded"
        />
      </div>
    </div>

    <!-- Multi-month Plans -->
    <div v-for="plan in plans" :key="plan.months" class="space-y-2">
      <div class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
        Your {{ plan.months }} Month Subscription Plan
      </div>
      
      <!-- Show either activation button or pricing form -->
      <div v-if="!plan.active">
        <button
          @click="togglePlan(plan.months)"
          :disabled="!subscriptionsEnabled"
          class="w-full px-4 py-2 text-sm text-white bg-blue-500 hover:bg-blue-600 rounded-md transition-colors text-left"
        >
          Activate {{ plan.months }} Month's Subscription Plan
        </button>
      </div>
      
      <div v-else class="space-y-3">
        <!-- Discount Selection -->
        <div class="flex items-center gap-2">
          <label class="text-xs text-text-light-secondary dark:text-text-dark-secondary">Discount:</label>
          <select
            v-model.number="plan.discount"
            @change="updatePlanPrice(plan)"
            class="px-2 py-1.5 text-sm bg-background-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded"
          >
            <option v-for="discount in discountOptions" :key="discount" :value="discount">
              {{ discount }}% Off
            </option>
          </select>
        </div>

        <!-- Price Display and Input -->
        <div class="flex items-center gap-2">
          <div class="relative flex-1">
            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-sm text-text-light-secondary dark:text-text-dark-secondary">$</span>
            <input
              v-model.number="plan.price"
              type="number"
              min="0"
              step="0.01"
              placeholder="Amount"
              class="w-full pl-6 pr-2 py-1.5 text-sm bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded"
            />
          </div>

          <button
            @click="togglePlan(plan.months)"
            class="p-1.5 text-red-500 hover:text-red-400"
          >
            <i class="ri-close-circle-line text-xl"></i>
          </button>
        </div>

        <!-- Price Breakdown -->
        <div v-if="plan.discount > 0" class="text-xs text-text-light-secondary dark:text-text-dark-secondary bg-gray-50 dark:bg-gray-800 p-2 rounded">
          <div>Original: ${{ (basePrice * plan.months).toFixed(2) }}</div>
          <div>Discount: {{ plan.discount }}%</div>
          <div>Final: ${{ plan.price.toFixed(2) }}</div>
        </div>
      </div>
    </div>

    <!-- Toggle Subscriptions -->
    <div class="flex items-center justify-between">
      <span class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">Enable Subscriptions</span>
      <ToggleSwitch v-model="subscriptionsEnabled" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import ToggleSwitch from '@/components/common/ToggleSwitch.vue';

const props = defineProps({
  basePrice: { type: [Number, String], required: true },
  twoMonthPrice: { type: [Number, String], required: true },
  threeMonthPrice: { type: [Number, String], required: true },
  sixMonthPrice: { type: [Number, String], required: true },
  twoMonthDiscount: { type: [Number, String], required: true },
  threeMonthDiscount: { type: [Number, String], required: true },
  sixMonthDiscount: { type: [Number, String], required: true },
  activePlans: { type: Array, required: true },
  subscriptionsEnabled: { type: Boolean, required: true }
});

const emit = defineEmits([
  'update:basePrice',
  'update:twoMonthPrice',
  'update:threeMonthPrice',
  'update:sixMonthPrice',
  'update:twoMonthDiscount',
  'update:threeMonthDiscount',
  'update:sixMonthDiscount',
  'update:activePlans',
  'update:subscriptionsEnabled'
]);

const basePrice = computed({
  get: () => props.basePrice || 0,
  set: (value) => {
    const newValue = value === '' ? 0 : Number(value);
    emit('update:basePrice', newValue);
    // Recalculate all plan prices when base price changes
    plans.value.forEach(plan => {
      if (plan.active && plan.discount > 0) {
        updatePlanPrice(plan);
      }
    });
  }
});

const plans = ref([
  { months: 2, price: props.twoMonthPrice, discount: props.twoMonthDiscount, active: props.activePlans.includes(2) },
  { months: 3, price: props.threeMonthPrice, discount: props.threeMonthDiscount, active: props.activePlans.includes(3) },
  { months: 6, price: props.sixMonthPrice, discount: props.sixMonthDiscount, active: props.activePlans.includes(6) }
]);

const subscriptionsEnabled = computed({
  get: () => props.subscriptionsEnabled,
  set: (value) => emit('update:subscriptionsEnabled', value)
});

const discountOptions = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

// Function to calculate discounted price
const calculateDiscountedPrice = (originalPrice, discountPercent) => {
  if (discountPercent <= 0) return originalPrice;
  return originalPrice * (1 - discountPercent / 100);
};

// Function to update plan price based on discount
const updatePlanPrice = (plan) => {
  if (plan.active && plan.discount > 0) {
    const originalPrice = basePrice.value * plan.months;
    plan.price = calculateDiscountedPrice(originalPrice, plan.discount);
  } else if (plan.active && plan.discount === 0) {
    plan.price = basePrice.value * plan.months;
  }
};

const togglePlan = (months) => {
  const plan = plans.value.find(p => p.months === months);
  if (plan) {
    plan.active = !plan.active;
    if (plan.active) {
      // Set default price when activating
      plan.price = basePrice.value * plan.months;
      plan.discount = 0;
    }
    updateActivePlans();
  }
};

const updateActivePlans = () => {
  const activePlans = plans.value.filter(plan => plan.active).map(plan => plan.months);
  activePlans.push(1); // Always include the 1-month plan
  emit('update:activePlans', activePlans);
};

// Watch for changes in plans and emit updates
watch(plans, () => {
  emit('update:twoMonthPrice', plans.value[0].price === '' ? 0 : Number(plans.value[0].price));
  emit('update:threeMonthPrice', plans.value[1].price === '' ? 0 : Number(plans.value[1].price));
  emit('update:sixMonthPrice', plans.value[2].price === '' ? 0 : Number(plans.value[2].price));
  emit('update:twoMonthDiscount', plans.value[0].discount === '' ? 0 : Number(plans.value[0].discount));
  emit('update:threeMonthDiscount', plans.value[1].discount === '' ? 0 : Number(plans.value[1].discount));
  emit('update:sixMonthDiscount', plans.value[2].discount === '' ? 0 : Number(plans.value[2].discount));
}, { deep: true });

// Watch base price changes to recalculate plan prices
watch(basePrice, (newValue) => {
  plans.value.forEach(plan => {
    if (plan.active) {
      updatePlanPrice(plan);
    }
  });
});

watch(subscriptionsEnabled, (newValue) => {
  if (!newValue) {
    plans.value.forEach(plan => {
      plan.active = false;
      plan.price = 0;
      plan.discount = 0;
    });
    updateActivePlans();
  }
});
</script>

