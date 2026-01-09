<template>
  <div class="relative hidden lg:block">
    <button @click="toggleDropdown" class="flex items-center gap-2">
      <div class="h-8 w-8 rounded-full overflow-hidden">
        <img src="/placeholder.svg?height=32&width=32" alt="Profile" class="w-full h-full object-cover" />
        <div class="absolute bottom-0 right-0 w-2 h-2 bg-accent-success rounded-full border-2 border-base-dark"></div>
      </div>
    </button>

    <!-- Dropdown Menu -->
    <div v-if="isOpen" class="absolute right-0 mt-2 w-72 max-h-[calc(100vh-80px)] overflow-y-auto rounded-md shadow-lg bg-surface-light dark:bg-surface-dark ring-1 ring-black ring-opacity-5 backdrop-blur-lg bg-opacity-90 dark:bg-opacity-90">
      <!-- Profile Header -->
      <div class="p-4 border-b border-border-dark">
        <div class="flex items-center gap-3 mb-3">
          <img src="/placeholder.svg?height=48&width=48" alt="Profile" class="h-12 w-12 rounded-full" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1">
              <span class="text-content-dark font-medium">{{ username }}</span>
              <i class="ri-checkbox-circle-fill text-accent text-sm"></i>
            </div>
            <div class="text-sm text-content-dark-light">{{ handle }}</div>
          </div>
        </div>
        <!-- Stats -->
        <div class="flex justify-between text-sm">
          <div class="text-center">
            <div class="text-content-dark font-medium">{{ likes }}</div>
            <div class="text-content-dark-light">Likes</div>
          </div>
          <div class="text-center">
            <div class="text-content-dark font-medium">{{ followers }}</div>
            <div class="text-content-dark-light">Followers</div>
          </div>
          <div class="text-center">
            <div class="text-content-dark font-medium">{{ subscribers }}</div>
            <div class="text-content-dark-light">Subscribers</div>
          </div>
        </div>
      </div>

      <!-- Menu Items -->
      <div class="py-1">
        <router-link 
          v-for="item in menuItems" 
          :key="item.label" 
          :to="item.to"
          class="flex items-center px-4 py-2 text-content-dark-light hover:bg-secondary-dark/50"
        >
          <i :class="item.icon" class="w-5 h-5 mr-3"></i>
          <span>{{ item.label }}</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/authStore';

const { t } = useI18n();
const authStore = useAuthStore();

const props = defineProps({
  username: String,
  handle: String,
  likes: Number,
  followers: Number,
  subscribers: Number,
});

const isOpen = ref(false);

const menuItems = [
  { label: t('profile'), to: `/${authStore.user?.username}`, icon: 'ri-user-line' },
  { label: t('subscriptions'), to: '/subscriptions', icon: 'ri-vip-crown-line' },
  { label: t('media_collection'), to: '/media', icon: 'ri-gallery-line' },
  { label: t('scheduled_post_queue'), to: '/posts/scheduled', icon: 'ri-time-line' },
  { label: t('lists'), to: '/dashboard/lists', icon: 'ri-list-check' },
  { label: t('bookmarks'), to: '/bookmarks', icon: 'ri-bookmark-line' },
  { label: t('messages'), to: '/messages', icon: 'ri-message-3-line' },
  { label: t('notifications'), to: '/notifications', icon: 'ri-notification-3-line' },
  { label: t('creator_dashboard'), to: '/dashboard', icon: 'ri-dashboard-line' },
  { label: t('earning_statistics'), to: '/dashboard/earnings', icon: 'ri-line-chart-line' },
  { label: t('profile_statistics'), to: '/dashboard/statistics', icon: 'ri-bar-chart-line' },
  { label: t('referrals'), to: '/referrals', icon: 'ri-team-line' },
  { label: t('add_payment_method'), to: '/settings/payments', icon: 'ri-bank-card-line' },
  { label: t('add_payout_method'), to: '/payout', icon: 'ri-money-dollar-box-line' },
];

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const closeDropdown = (event) => {
  if (!event.target.closest('.relative')) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>

