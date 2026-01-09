<template>
  <Modal v-if="isOpen" @close="$emit('close')">
    <div class="w-full bg-surface-light dark:bg-surface-dark rounded-lg overflow-hidden shadow-xl">
      <!-- Close button -->
      <div class="absolute top-0 right-0 pt-4 pr-4">
        <button @click="$emit('close')" type="button" class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <span class="sr-only">Close</span>
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Profile Images Grid -->
      <div class="grid grid-cols-5 h-24">
        <img 
          v-for="(image, index) in profileImages" 
          :key="index"
          :src="image || '/placeholder.svg?height=96&width=96'"
          :alt="tier?.title"
          class="w-full h-full object-cover"
        />
      </div>

      <!-- Creator Info -->
      <div class="p-4 text-center">
        <div class="flex items-center justify-center gap-1 text-text-light-primary dark:text-text-dark-primary">
          <span class="text-xl font-semibold">{{ tier.creator.name }}</span>
          <i v-if="tier.creator.verified" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark"></i>
        </div>
        <div class="text-text-light-secondary dark:text-text-dark-secondary text-sm">@{{ tier.creator.handle }}</div>
      </div>

      <!-- Tier Info -->
      <div class="px-6 pb-6">
        <h2 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary text-center mb-4">
          {{ tier.title }}
        </h2>
        <h3 class="text-lg text-text-light-secondary dark:text-text-dark-secondary text-center mb-6">
          {{ $t('subscription_benefits') }}
        </h3>

        <!-- Benefits List -->
        <div v-if="tier.subscription_benefits && tier.subscription_benefits.length > 0" class="space-y-3 mb-6">
          <div 
            v-for="(benefit, index) in tier.subscription_benefits" 
            :key="index"
            class="flex items-start gap-2 text-text-light-primary dark:text-text-dark-primary"
          >
            <i class="ri-checkbox-circle-fill text-success mt-1"></i>
            <span>{{ benefit }}</span>
          </div>
        </div>
        <div v-else class="text-center text-text-light-secondary dark:text-text-dark-secondary mb-6">
          {{ $t('no_benefits_listed') }}
        </div>

        <!-- Subscribe Button -->
        <button 
          @click="handleSubscribe"
          class="w-full py-3 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover text-white rounded-md transition-colors mb-4"
        >
          {{ $t('subscribe_for_amount', { amount: tier.base_price }) }}
        </button>

        <!-- Auto-renewal Notice -->
        <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary text-center">
          {{ $t('auto_renewal_notice', { amount: tier.base_price }) }}
        </p>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';
import Modal from '@/components/common/Modal.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  tier: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'subscribe']);

const profileImages = computed(() => {
  const images = [];
  if (props.tier.creator.avatar) {
    images.push(props.tier.creator.avatar);
  }
  if (props.tier.creator.cover_photo) {
    images.push(props.tier.creator.cover_photo);
  }
  // Fill remaining slots with placeholder images
  while (images.length < 5) {
    images.push('/placeholder.svg?height=96&width=96');
  }
  return images;
});

const handleSubscribe = () => {
  emit('subscribe', props.tier);
};
</script>

