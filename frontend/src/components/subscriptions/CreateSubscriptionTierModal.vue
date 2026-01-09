<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-background-light dark:bg-background-dark">
    <div class="flex items-center justify-between p-4 border-b border-border-light dark:border-border-dark">
      <h2 class="text-base font-medium text-text-light-primary dark:text-text-dark-primary">
        Create Subscription Tier
      </h2>
      <button @click="$emit('close')" class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary">
        <i class="ri-close-line text-xl"></i>
      </button>
    </div>

    <div class="p-4 space-y-4">
      <div class="space-y-4">
        <div class="space-y-1">
          <label class="text-sm text-text-light-primary dark:text-text-dark-primary">Subscription Tier Name</label>
          <input v-model="formData.title" type="text" placeholder="Tier Title" class="w-full px-2 py-1.5 text-sm bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded">
        </div>

        <div class="space-y-1">
          <label class="text-sm text-text-light-primary dark:text-text-dark-primary">Subscription Tier Color</label>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full cursor-pointer" :style="{ backgroundColor: formData.color_code }" @click="showColorPicker = !showColorPicker"></div>
            <span class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ formData.color_code }}</span>
            <div v-if="showColorPicker" class="absolute z-10 p-2 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark">
              <div class="grid grid-cols-6 gap-1">
                <button v-for="color in predefinedColors" :key="color" @click="selectColor(color)" class="w-6 h-6 rounded-full" :style="{ backgroundColor: color }"></button>
              </div>
              <input type="color" v-model="formData.color_code" class="mt-2 w-full h-8 cursor-pointer">
            </div>
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-sm text-text-light-primary dark:text-text-dark-primary">Subscription Benefits</label>
          <div v-for="(benefit, index) in formData.subscription_benefits" :key="index" class="flex items-center gap-2">
            <input v-model="formData.subscription_benefits[index]" type="text" placeholder="Benefit" class="flex-1 px-2 py-1.5 text-sm bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded">
            <button @click="removeBenefit(index)" class="text-accent-danger hover:text-accent-danger-hover">
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
          <button @click="addBenefit" class="w-full py-2 mt-2 text-sm text-white bg-primary-light dark:bg-primary-dark hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover rounded transition-colors">
            + Add Benefit
          </button>
        </div>


        <div class="space-y-1">
          <label class="text-sm text-text-light-primary dark:text-text-dark-primary">Maximum Subscribers</label>
          <div class="flex items-center justify-between">
            <span class="text-sm text-text-light-primary dark:text-text-dark-primary">Enable Subscriber Limit</span>
            <ToggleSwitch v-model="formData.max_subscribers_enabled" />
          </div>
          <input v-if="formData.max_subscribers_enabled" v-model.number="formData.max_subscribers" type="number" min="1" placeholder="Max subscribers" class="w-full px-2 py-1.5 text-sm bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded">
        </div>

        <PricingTabContent 
          v-model:base-price="formData.base_price"
          v-model:two-month-price="formData.two_month_price"
          v-model:three-month-price="formData.three_month_price"
          v-model:six-month-price="formData.six_month_price"
          v-model:two-month-discount="formData.two_month_discount"
          v-model:three-month-discount="formData.three_month_discount"
          v-model:six-month-discount="formData.six_month_discount"
          v-model:active-plans="formData.active_plans"
          v-model:subscriptions-enabled="formData.subscriptions_enabled"
        />
      </div>

      <div v-if="Object.keys(errors).length > 0" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <ul class="list-disc list-inside">
          <li v-for="(errorList, field) in errors" :key="field">
            {{ errorList.join(', ') }}
          </li>
        </ul>
      </div>

      <div class="mt-4">
        <button @click="handleSubmit" :disabled="!isValid" class="px-6 py-1.5 text-sm text-white bg-primary-light dark:bg-primary-dark hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover disabled:opacity-50 disabled:cursor-not-allowed rounded transition-colors">
          Create
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import ToggleSwitch from '@/components/common/ToggleSwitch.vue';
import PricingTabContent from './PricingTabContent.vue';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
  isOpen: { type: Boolean, required: true }
});

const emit = defineEmits(['close', 'create']);

// Dynamic import for subscription store
let subscriptionStore = null;

const toast = useToast();

const formData = ref({
  title: '',
  color_code: '#3B82F6', // Default color (blue)
  subscription_benefits: [''],
  base_price: 0,
  two_month_price: 0,
  three_month_price: 0,
  six_month_price: 0,
  two_month_discount: 0,
  three_month_discount: 0,
  six_month_discount: 0,
  active_plans: [1],
  subscriptions_enabled: true,
  max_subscribers: null,
  max_subscribers_enabled: false
});

const showColorPicker = ref(false);
const formChanged = ref(false);
const errors = ref({});
const isSubmitting = ref(false);

const predefinedColors = [
  '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', 
  '#EC4899', '#6366F1', '#14B8A6', '#F97316', '#06B6D4', 
  '#84CC16', '#9333EA'
];

const selectColor = (color) => {
  formData.value.color_code = color;
  showColorPicker.value = false;
};

const addBenefit = () => {
  formData.value.subscription_benefits.push('');
};

const removeBenefit = (index) => {
  formData.value.subscription_benefits.splice(index, 1);
};

const isValid = computed(() => {
  const basePrice = typeof formData.value.base_price === 'string' 
    ? Number(formData.value.base_price) 
    : formData.value.base_price;

  return formData.value.title.trim() && 
         formData.value.subscription_benefits.some(benefit => benefit.trim() !== '') &&
         basePrice > 0 &&
         formData.value.color_code;
});

const handleSubmit = async () => {
  if (!isValid.value) return;

  errors.value = {}; // Clear previous errors
  isSubmitting.value = true; // Add loading state

  // Load subscription store dynamically
  if (!subscriptionStore) {
    const { useSubscriptionStore } = await import('@/stores/subscriptionStore');
    subscriptionStore = useSubscriptionStore();
  }

  // Transform the data to ensure all numeric values are numbers
  const transformedData = {
    ...formData.value,
    base_price: Number(formData.value.base_price) || 0,
    two_month_price: Number(formData.value.two_month_price) || 0,
    three_month_price: Number(formData.value.three_month_price) || 0,
    six_month_price: Number(formData.value.six_month_price) || 0,
    two_month_discount: Number(formData.value.two_month_discount) || 0,
    three_month_discount: Number(formData.value.three_month_discount) || 0,
    six_month_discount: Number(formData.value.six_month_discount) || 0,
  };

  try {
    const result = await subscriptionStore.createTier(transformedData);
    console.log('Create tier result:', result);
    
    if (result.success) {
      toast.success(result.message || 'Subscription tier created successfully');
      emit('create', result.data);
      emit('close');
    } else {
      // Handle validation errors
      if (result.error?.errors) {
        errors.value = result.error.errors;
        Object.keys(result.error.errors).forEach(key => {
          toast.error(result.error.errors[key][0]);
        });
      } else {
        errors.value = { general: [result.message || 'Failed to create tier'] };
        toast.error(result.message || 'Failed to create tier');
      }
    }
  } catch (error) {
    console.error('An unexpected error occurred:', error);
    errors.value = { general: ['An unexpected error occurred while creating the tier'] };
    toast.error('An unexpected error occurred while creating the tier');
  } finally {
    isSubmitting.value = false;
  }
};

watch(formData, () => {
  formChanged.value = true;
}, { deep: true });
</script>

