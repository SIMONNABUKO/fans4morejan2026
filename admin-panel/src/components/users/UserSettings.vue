<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/50 p-4 rounded-md">
      <p class="text-red-700 dark:text-red-200">{{ error }}</p>
    </div>

    <div v-else>
      <!-- Account Settings -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Account Settings</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
          <dl class="grid grid-cols-1 gap-4">
            <div v-for="(value, key) in settings.account" :key="key" class="sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ formatLabel(key) }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <input
                  v-if="key === 'phone'"
                  type="tel"
                  v-model="settings.account[key]"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  @change="updateSettings('account', { [key]: settings.account[key] })"
                />
                <select
                  v-else-if="key === 'language'"
                  v-model="settings.account[key]"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  @change="updateSettings('account', { [key]: settings.account[key] })"
                >
                  <option value="en">English</option>
                  <option value="es">Spanish</option>
                  <option value="fr">French</option>
                </select>
                <input
                  v-else-if="key === 'timezone'"
                  type="text"
                  v-model="settings.account[key]"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  @change="updateSettings('account', { [key]: settings.account[key] })"
                />
                <span v-else>{{ value }}</span>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Privacy & Security Settings -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Privacy & Security</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
          <dl class="grid grid-cols-1 gap-4">
            <div v-for="(value, key) in settings.privacyAndSecurity" :key="key" class="sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ formatLabel(key) }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  :class="value ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                  @click="toggleSetting('privacyAndSecurity', key)"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="value ? 'translate-x-5' : 'translate-x-0'"
                  />
                </button>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Email Notification Settings -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Notifications</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
          <dl class="grid grid-cols-1 gap-4">
            <div v-for="(value, key) in settings.emailNotifications" :key="key" class="sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ formatLabel(key) }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  :class="value ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                  @click="toggleSetting('emailNotifications', key)"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="value ? 'translate-x-5' : 'translate-x-0'"
                  />
                </button>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Messaging Settings -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Messaging Settings</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
          <dl class="grid grid-cols-1 gap-4">
            <div v-for="(value, key) in settings.messaging" :key="key" class="sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ formatLabel(key) }}</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                  :class="value ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'"
                  @click="toggleSetting('messaging', key)"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="value ? 'translate-x-5' : 'translate-x-0'"
                  />
                </button>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from '../../plugins/axios'

interface UserSettings {
  account: {
    username: string
    email: string
    phone: string
    language: string
    timezone: string
  }
  privacyAndSecurity: {
    twoFactorAuth: boolean
    privateAccount: boolean
    showActivity: boolean
    pushNotifications: boolean
  }
  emailNotifications: {
    new_messages: boolean
    subscription_purchases: boolean
    subscription_renews: boolean
    subscription_gift_link_purchases: boolean
    media_purchases: boolean
    media_bundle_purchases: boolean
    media_likes: boolean
    post_replies: boolean
    post_likes: boolean
    tips_received: boolean
    new_followers: boolean
    stream_live: boolean
  }
  messaging: {
    show_read_receipts: boolean
    require_tip_for_messages: boolean
    accept_messages_from_followed: boolean
  }
}

const props = defineProps<{
  userId: number
}>()

const settings = ref<UserSettings>({
  account: {
    username: '',
    email: '',
    phone: '',
    language: 'en',
    timezone: 'UTC'
  },
  privacyAndSecurity: {
    twoFactorAuth: false,
    privateAccount: false,
    showActivity: true,
    pushNotifications: false
  },
  emailNotifications: {
    new_messages: true,
    subscription_purchases: true,
    subscription_renews: true,
    subscription_gift_link_purchases: true,
    media_purchases: true,
    media_bundle_purchases: true,
    media_likes: true,
    post_replies: true,
    post_likes: true,
    tips_received: true,
    new_followers: true,
    stream_live: true
  },
  messaging: {
    show_read_receipts: false,
    require_tip_for_messages: false,
    accept_messages_from_followed: true
  }
})

const loading = ref(false)
const error = ref<string | null>(null)

onMounted(async () => {
  await fetchSettings()
})

async function fetchSettings() {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get(`/admin/users/${props.userId}/settings`)
    settings.value = response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load user settings'
    console.error('Error loading user settings:', err)
  } finally {
    loading.value = false
  }
}

async function updateSettings(category: keyof UserSettings, updates: Record<string, any>) {
  loading.value = true
  error.value = null
  try {
    await axios.put(`/admin/users/${props.userId}/settings/${category}`, updates)
    await fetchSettings() // Refresh settings after update
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update settings'
    console.error('Error updating settings:', err)
    await fetchSettings() // Refresh settings to revert changes on error
  } finally {
    loading.value = false
  }
}

async function toggleSetting(category: keyof UserSettings, key: string) {
  const currentValue = settings.value[category][key as keyof typeof settings.value[typeof category]]
  await updateSettings(category, { [key]: !currentValue })
}

function formatLabel(key: string): string {
  return key
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}
</script> 