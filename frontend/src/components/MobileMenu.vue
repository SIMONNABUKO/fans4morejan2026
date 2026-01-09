<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 lg:hidden"
    @click="close"
  >
    <div class="absolute inset-0 bg-gray-900 bg-opacity-50"></div>
    <div 
      class="absolute top-0 left-0 bottom-0 w-64 bg-base-dark text-content-dark overflow-y-auto"
      @click.stop
    >
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
            <div class="text-content-dark-light">{{ t('likes') }}</div>
          </div>
          <div class="text-center">
            <div class="text-content-dark font-medium">{{ followers }}</div>
            <div class="text-content-dark-light">{{ t('followers') }}</div>
          </div>
          <div class="text-center">
            <div class="text-content-dark font-medium">{{ subscribers }}</div>
            <div class="text-content-dark-light">{{ t('subscribers') }}</div>
          </div>
        </div>
      </div>

      <!-- Menu Items -->
      <div class="py-2">
        <a 
          v-for="item in menuItems" 
          :key="item.label" 
          :href="item.href" 
          class="flex items-center px-4 py-2 text-content-dark-light hover:bg-secondary-dark"
        >
          <i :class="item.icon" class="w-5 h-5 mr-3"></i>
          <span>{{ item.label }}</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  isOpen: Boolean,
  username: String,
  handle: String,
  likes: Number,
  followers: Number,
  subscribers: Number,
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};

const menuItems = [
  { label: t('profile'), href: '#', icon: 'ri-user-line' },
  { label: t('subscriptions'), href: '#', icon: 'ri-vip-crown-line' },
  { label: t('media_collection'), href: '#', icon: 'ri-gallery-line' },
  { label: t('scheduled_post_queue'), href: '#', icon: 'ri-time-line' },
  { label: t('lists'), href: '#', icon: 'ri-list-check' },
  { label: t('bookmarks'), href: '#', icon: 'ri-bookmark-line' },
  { label: t('messages'), href: '#', icon: 'ri-message-3-line' },
  { label: t('notifications'), href: '#', icon: 'ri-notification-3-line' },
  { label: t('creator_dashboard'), href: '#', icon: 'ri-dashboard-line' },
  { label: t('earning_statistics'), href: '#', icon: 'ri-line-chart-line' },
  { label: t('profile_statistics'), href: '#', icon: 'ri-bar-chart-line' },
  { label: t('referrals'), href: '/referrals', icon: 'ri-team-line' },
  { label: t('add_payment_method'), href: '#', icon: 'ri-bank-card-line' },
  { label: t('add_payout_method'), href: '#', icon: 'ri-money-dollar-box-line' },
];
</script>

