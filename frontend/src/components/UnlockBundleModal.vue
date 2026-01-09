<template>
  <div v-if="subscriptionStore.enabledTiers.length > 0" class="subscription-tiers">
    <div v-for="tier in subscriptionStore.enabledTiers" :key="tier.id" class="tier-option">
      <h3 class="text-lg font-medium">{{ tier.name }}</h3>
      <p class="text-sm text-gray-500">{{ t('subscription_benefits') }}</p>
      <ul class="mt-2 space-y-2">
        <li v-for="benefit in tier.benefits" :key="benefit" class="flex items-center">
          <i class="ri-check-line text-green-500 mr-2"></i>
          <span>{{ benefit }}</span>
        </li>
        <li v-if="!tier.benefits || tier.benefits.length === 0" class="text-gray-500">
          {{ t('no_benefits_listed') }}
        </li>
      </ul>
      <button 
        @click="handleSubscribe(tier)"
        :disabled="loading"
        class="mt-4 w-full px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:opacity-90 disabled:opacity-50"
      >
        <span v-if="loading" class="flex items-center justify-center">
          <i class="ri-loader-4-line animate-spin mr-2"></i>
          {{ t('processing') }}
        </span>
        <span v-else>
          {{ t('subscribe_for_amount', { amount: tier.price }) }}
        </span>
      </button>
      <p class="mt-2 text-xs text-gray-500">
        {{ t('auto_renewal_notice', { amount: tier.price }) }}
      </p>
      <p v-if="error" class="mt-2 text-sm text-accent-danger">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useSubscriptionStore } from '@/stores/subscriptionStore';
import { useI18n } from 'vue-i18n';
import { useTrackingLink } from '@/composables/useTrackingLink';
import { useToast } from 'vue-toast-notification';

const { t } = useI18n();
const subscriptionStore = useSubscriptionStore();
const { trackSubscription } = useTrackingLink();
const toast = useToast();

const loading = ref(false);
const error = ref('');

const hasSubscriptionTiers = computed(() => subscriptionStore.enabledTiers.length > 0);

const handleSubscribe = async (tier) => {
  loading.value = true;
  error.value = '';
  
  try {
    // Track the subscription
    await trackSubscription(tier.id, tier.duration || 30);
    
    // Proceed with subscription
    await subscriptionStore.subscribeToTier(tier.id);
    
    toast.success(t('subscription_successful'));
  } catch (error) {
    console.error('Error subscribing to tier:', error);
    error.value = error.response?.data?.message || t('subscription_failed');
    toast.error(error.value);
  } finally {
    loading.value = false;
  }
};
</script> 