<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/settings" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ t('notifications') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your notification preferences
            </p>
          </div>
        </div>
        
        <!-- Right Side: Notification Status -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Status</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">
              {{ settingsStore.privacyAndSecurity.pushNotifications ? 'Enabled' : 'Disabled' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Push Notifications Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-notification-line text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('push_notifications') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('enable_push_notifications_to_unlock_settings') }}
            </p>
          </div>
          <div class="flex-shrink-0">
            <button 
              @click="togglePushNotifications"
              class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :class="settingsStore.privacyAndSecurity.pushNotifications ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span 
                class="inline-block h-6 w-6 transform rounded-full bg-white shadow-sm transition-transform duration-200"
                :class="settingsStore.privacyAndSecurity.pushNotifications ? 'translate-x-7' : 'translate-x-1'"
              ></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Email Notifications Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-mail-line text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('email_notifications') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Choose which email notifications you want to receive
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Loading State -->
        <div v-if="settingsStore.isLoading" class="text-center py-8">
          <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-500 dark:text-gray-400">{{ t('loading') }}...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="settingsStore.error" class="text-center py-8">
          <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-2xl"></i>
          </div>
          <p class="text-red-600 dark:text-red-400 font-medium">{{ settingsStore.error }}</p>
        </div>
        
        <!-- Settings List -->
        <div v-else class="space-y-4">
          <div 
            v-for="setting in emailSettingsArray" 
            :key="setting.id" 
            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                <i class="ri-settings-3-line text-gray-600 dark:text-gray-400 text-sm"></i>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ setting.label }}</span>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  {{ getSettingDescription(setting.id) }}
                </p>
              </div>
            </div>
            <button 
              @click="toggleEmailSetting(setting.id)"
              class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
              :class="settingsStore.emailNotifications[setting.id] ? 'bg-green-600 dark:bg-green-500' : 'bg-gray-200 dark:bg-gray-700'"
            >
              <span 
                class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform duration-200"
                :class="settingsStore.emailNotifications[setting.id] ? 'translate-x-6' : 'translate-x-1'"
              ></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useSettingsStore } from '@/stores/settingsStore'
import { useI18n } from 'vue-i18n'

const settingsStore = useSettingsStore()
const { t } = useI18n()

const emailSettingsArray = computed(() => [
  { id: 'new_messages', label: t('new_messages') },
  { id: 'subscription_purchases', label: t('subscription_purchases') },
  { id: 'subscription_renews', label: t('subscription_renews') },
  { id: 'media_purchases', label: t('media_purchases') },
  { id: 'media_likes', label: t('media_likes') },
  { id: 'post_replies', label: t('post_replies') },
  { id: 'post_likes', label: t('post_likes') },
  { id: 'tips_received', label: t('tips_received') },
  { id: 'new_followers', label: t('new_followers') },
])

const enabledEmailSettingsCount = computed(() => {
  return emailSettingsArray.value.filter(setting => 
    settingsStore.emailNotifications[setting.id]
  ).length
})

const getSettingDescription = (settingId) => {
  const descriptions = {
    new_messages: 'Get notified when you receive new messages',
    subscription_purchases: 'Notifications for new subscription purchases',
    subscription_renews: 'Notifications when subscriptions renew',
    media_purchases: 'Notifications for media purchases',
    media_likes: 'Notifications when your media gets liked',
    post_replies: 'Notifications for post replies',
    post_likes: 'Notifications when your posts get liked',
    tips_received: 'Notifications when you receive tips',
    new_followers: 'Notifications for new followers'
  }
  return descriptions[settingId] || 'Email notification setting'
}

onMounted(async () => {
  await settingsStore.fetchCategorySettings('privacyAndSecurity')
  await settingsStore.fetchCategorySettings('emailNotifications')
})

const togglePushNotifications = async () => {
  try {
    await settingsStore.toggleSetting('privacyAndSecurity', 'pushNotifications')
  } catch (error) {
    console.error('Failed to toggle push notifications:', error)
  }
}

const toggleEmailSetting = async (key) => {
  try {
    await settingsStore.toggleSetting('emailNotifications', key)
  } catch (error) {
    console.error('Failed to toggle email setting:', error)
  }
}
</script>

