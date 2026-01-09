<template>
  <transition name="fade">
    <div v-if="show" 
      class="fixed top-4 right-4 z-50 bg-surface-light dark:bg-surface-dark shadow-lg rounded-lg p-4 max-w-md border border-border-light dark:border-border-dark">
      <div class="flex items-start">
        <!-- Follow notification icon -->
        <div v-if="notification && notification.type === 'follow'" class="mr-3 flex-shrink-0">
          <div class="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
            <i class="ri-user-follow-line text-blue-500 dark:text-blue-400"></i>
          </div>
        </div>
        
        <!-- Like notification icon -->
        <div v-else-if="notification && notification.type === 'like'" class="mr-3 flex-shrink-0">
          <div class="bg-red-100 dark:bg-red-900 p-2 rounded-full">
            <i class="ri-heart-3-fill text-red-500 dark:text-red-400"></i>
          </div>
        </div>

        <!-- Creator application notification icon -->
        <div v-else-if="notification && notification.data && ['creator_application_approved', 'creator_application_rejected'].includes(notification.data.type)" class="mr-3 flex-shrink-0">
          <div :class="[
            'p-2 rounded-full',
            notification.data.type === 'creator_application_approved' 
              ? 'bg-green-100 dark:bg-green-900' 
              : 'bg-red-100 dark:bg-red-900'
          ]">
            <i :class="[
              notification.data.type === 'creator_application_approved' 
                ? 'ri-check-double-line text-green-500 dark:text-green-400'
                : 'ri-close-line text-red-500 dark:text-red-400'
            ]"></i>
          </div>
        </div>
        
        <!-- Default notification icon -->
        <div v-else class="mr-3 flex-shrink-0">
          <div class="bg-primary-light/10 dark:bg-primary-dark/20 p-2 rounded-full">
            <i class="ri-notification-3-fill text-primary-light dark:text-primary-dark"></i>
          </div>
        </div>
        
        <div class="flex-1">
          <div class="flex justify-between items-start">
            <p class="font-medium">
              <span class="text-primary-light dark:text-primary-dark">{{ t('fansformore') }}:</span>
              {{ notification ? (notification.data?.message || t('new_notification')) : t('new_notification') }}
            </p>
            <button @click="dismiss" class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary">
              <i class="ri-close-line"></i>
            </button>
          </div>

          <!-- Show feedback for creator application notifications -->
          <div v-if="notification && notification.data && ['creator_application_approved', 'creator_application_rejected'].includes(notification.data.type) && notification.data.feedback" 
               :class="[
                 'mt-2 px-3 py-1 rounded-lg text-sm inline-block',
                 notification.data.type === 'creator_application_approved' 
                   ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200' 
                   : 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200'
               ]"
          >
            <i :class="[
              notification.data.type === 'creator_application_approved' ? 'ri-check-double-line' : 'ri-information-line',
              'mr-1'
            ]"></i>
            {{ notification.data.feedback }}
          </div>

          <!-- Show status for tag requests -->
          <div v-if="notification && notification.data && notification.data.type === 'tag_request' && notification.data.tag && notification.data.tag.status" 
               :class="[
                 'mt-2 px-3 py-1 rounded-lg text-sm inline-block',
                 notification.data.tag.status.toLowerCase() === 'approved' ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200' :
                 notification.data.tag.status.toLowerCase() === 'rejected' ? 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200' : ''
               ]"
          >
            <i :class="[
              notification.data.tag.status.toLowerCase() === 'approved' ? 'ri-check-double-line' : 'ri-close-line',
              'mr-1'
            ]"></i>
            {{ t(notification.data.tag.status.toLowerCase()) }}
          </div>
          <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mt-1">
            {{ timeAgo }}
          </p>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  notification: {
    type: Object,
    default: () => null
  },
  show: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['dismiss']);

const dismiss = () => {
  emit('dismiss');
};

const timeAgo = computed(() => {
  if (!props.notification || (!props.notification.created_at && !props.notification.time)) {
    return t('just_now');
  }
  
  const timestamp = props.notification.created_at || props.notification.time;
  const date = new Date(timestamp);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) {
    return t('just_now');
  } else if (seconds < 3600) {
    const minutes = Math.floor(seconds / 60);
    return t('minutes_ago', { minutes });
  } else if (seconds < 86400) {
    const hours = Math.floor(seconds / 3600);
    return t('hours_ago', { hours });
  } else {
    const days = Math.floor(seconds / 86400);
    return t('days_ago', { days });
  }
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>