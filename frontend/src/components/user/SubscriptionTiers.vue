<template>
  <div class="p-4 border-b border-border-light dark:border-border-dark">
    <button 
      v-if="activeTiers.length === 0 && isCreator"
      @click="handleSetupTiers"
      class="px-4 py-2 mb-4 font-medium rounded-md
             bg-primary-light dark:bg-primary-dark
             text-white
             hover:bg-primary-light/90 dark:hover:bg-primary-dark/90
             focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:ring-offset-2
             transition-colors duration-200 ease-in-out"
    >
      {{ t('set_up_subscription_tiers') }}
    </button>

    <!-- Display tiers when they exist -->
    <div v-if="activeTiers.length > 0 && isCreator" class="space-y-4 mb-4">
      <div v-for="tier in activeTiers" :key="tier.id" class="space-y-2">
        <div class="font-medium text-lg text-text-light-primary dark:text-text-dark-primary">
          {{ tier.title }}
        </div>
        
        <!-- Main subscription option -->
        <button 
          class="w-full px-4 py-3 text-left rounded-md text-white transition-colors"
          :style="{
            backgroundColor: tier.color_code,
            '--hover-color': adjustColor(tier.color_code, -10),
          }"
          @click="handleTierClick(tier)"
          @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
          @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
        >
          <div class="flex justify-between items-center">
            <span>{{ tier.title }} {{ t('1_month') }}</span>
            <span>${{ tier.base_price }}</span>
          </div>
        </button>

        <!-- Additional Plans Section -->
        <div v-if="hasAdditionalPlans(tier)" class="space-y-2">
          <button 
            @click="toggleAdditionalPlans(tier.id)"
            class="w-full flex items-center justify-between px-2 py-1.5 text-sm text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors"
          >
            <span class="flex items-center gap-2">
              <i class="ri-time-line"></i>
              {{ t('additional_plans') }}
            </span>
            <i :class="[
              expandedTiers.has(tier.id) ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line',
              'text-xl transition-transform'
            ]"></i>
          </button>

          <!-- Additional Plans Content -->
          <div v-if="expandedTiers.has(tier.id)" class="space-y-2">
            <button 
              v-if="tier.two_month_price > 0"
              class="w-full px-4 py-3 text-left rounded-md text-white transition-colors"
              :style="{
                backgroundColor: tier.color_code,
                '--hover-color': adjustColor(tier.color_code, -10),
              }"
              @click="handleSubscribe(tier.id, 2)"
              @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
              @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
            >
              <div class="flex justify-between items-center">
                <span>{{ tier.title }} {{ t('2_months') }}</span>
                <div class="flex items-center gap-2">
                  <span>${{ tier.two_month_price }}</span>
                  <span class="text-xs bg-white/20 px-1.5 py-0.5 rounded">
                    {{ tier.two_month_discount }}% {{ t('off') }}!
                  </span>
                </div>
              </div>
            </button>

            <button 
              v-if="tier.three_month_price > 0"
              class="w-full px-4 py-3 text-left rounded-md text-white transition-colors"
              :style="{
                backgroundColor: tier.color_code,
                '--hover-color': adjustColor(tier.color_code, -10),
              }"
              @click="handleSubscribe(tier.id, 3)"
              @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
              @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
            >
              <div class="flex justify-between items-center">
                <span>{{ tier.title }} {{ t('3_months') }}</span>
                <div class="flex items-center gap-2">
                  <span>${{ tier.three_month_price }}</span>
                  <span class="text-xs bg-white/20 px-1.5 py-0.5 rounded">
                    {{ tier.three_month_discount }}% {{ t('off') }}!
                  </span>
                </div>
              </div>
            </button>

            <button 
              v-if="tier.six_month_price > 0"
              class="w-full px-4 py-3 text-left rounded-md text-white transition-colors"
              :style="{
                backgroundColor: tier.color_code,
                '--hover-color': adjustColor(tier.color_code, -10),
              }"
              @click="handleSubscribe(tier.id, 6)"
              @mouseenter="e => e.target.style.backgroundColor = adjustColor(tier.color_code, -10)"
              @mouseleave="e => e.target.style.backgroundColor = tier.color_code"
            >
              <div class="flex justify-between items-center">
                <span>{{ tier.title }} {{ t('6_months') }}</span>
                <div class="flex items-center gap-2">
                  <span>${{ tier.six_month_price }}</span>
                  <span class="text-xs bg-white/20 px-1.5 py-0.5 rounded">
                    {{ tier.six_month_discount }}% {{ t('off') }}!
                  </span>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <button 
      v-if="isCreator"
      @click="openCreateAlbumModal"
      class="flex items-center gap-2 text-primary-light dark:text-primary-dark hover:text-primary-light/80 dark:hover:text-primary-dark/80 transition-colors"
    >
      <i class="ri-add-line text-xl"></i>
      <span>{{ t('new') }}</span>
    </button>

    <CreateAlbumModal 
      :is-open="isCreateAlbumModalOpen"
      @close="closeCreateAlbumModal"
      @create="handleCreateAlbum"
    />
    <SubscriptionTierModal
      v-if="showSubscriptionModal"
      :tier="selectedTier"
      @close="closeSubscriptionModal"
      @subscribe="handleSubscribe"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useSubscriptionStore } from '@/stores/subscriptionStore';
import { useAuthStore } from '@/stores/authStore';
import CreateAlbumModal from '@/components/media/CreateAlbumModal.vue';
import SubscriptionTierModal from '@/components/subscriptions/SubscriptionTierModal.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  isOwner: {
    type: Boolean,
    default: false
  }
});

const router = useRouter();
const subscriptionStore = useSubscriptionStore();
const authStore = useAuthStore();
const isCreateAlbumModalOpen = ref(false);
const expandedTiers = ref(new Set());
const showSubscriptionModal = ref(false);
const selectedTier = ref(null);

onMounted(async () => {
  await subscriptionStore.fetchTiers();
  console.log('Component mounted. isOwner:', props.isOwner);
});

// Get active tiers from the store
const activeTiers = computed(() => subscriptionStore.enabledTiers);

const isCreator = computed(() => authStore.user && authStore.user.role === 'creator');

const adjustColor = (hex, percent) => {
  hex = hex.replace('#', '');
  let r = parseInt(hex.substring(0, 2), 16);
  let g = parseInt(hex.substring(2, 4), 16);
  let b = parseInt(hex.substring(4, 6), 16);
  r = Math.max(0, Math.min(255, r + (r * percent / 100)));
  g = Math.max(0, Math.min(255, g + (g * percent / 100)));
  b = Math.max(0, Math.min(255, b + (b * percent / 100)));
  return '#' + 
    Math.round(r).toString(16).padStart(2, '0') +
    Math.round(g).toString(16).padStart(2, '0') +
    Math.round(b).toString(16).padStart(2, '0');
};

const hasAdditionalPlans = (tier) => {
  return tier.two_month_price > 0 || tier.three_month_price > 0 || tier.six_month_price > 0;
};

const handleSetupTiers = () => {
  router.push('/dashboard/plans');
};

const toggleAdditionalPlans = (tierId) => {
  if (expandedTiers.value.has(tierId)) {
    expandedTiers.value.delete(tierId);
  } else {
    expandedTiers.value.add(tierId);
  }
};

const handleSubscribe = async (tierId, duration) => {
  try {
    const result = await subscriptionStore.subscribeTier(tierId, duration);
    if (result.success) {
      console.log('Subscribed successfully:', result.subscription);
    } else {
      console.error('Failed to subscribe:', result.error);
    }
  } catch (error) {
    console.error('An unexpected error occurred while subscribing:', error);
  }
};

const openCreateAlbumModal = () => {
  isCreateAlbumModalOpen.value = true;
};

const closeCreateAlbumModal = () => {
  isCreateAlbumModalOpen.value = false;
};

const handleCreateAlbum = (albumData) => {
  console.log('New album data:', albumData);
  closeCreateAlbumModal();
};

const openSubscriptionModal = (tier) => {
  selectedTier.value = tier;
  showSubscriptionModal.value = true;
};

const closeSubscriptionModal = () => {
  showSubscriptionModal.value = false;
  selectedTier.value = null;
};

const handleTierClick = (tier) => {
  if (authStore.user && authStore.user.id === tier.creator.id) {
    console.log('You are the creator of this tier');
    return;
  }
  openSubscriptionModal(tier);
};
</script>

