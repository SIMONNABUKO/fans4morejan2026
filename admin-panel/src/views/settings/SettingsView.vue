<template>
  <div class="min-h-full">
    <div class="py-6 w-full">
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Settings</h1>
      </div>
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="mt-6 space-y-6 w-full">
          <!-- General Settings -->
          <div class="w-full overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="w-full px-4 py-5 sm:p-6">
              <h2 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">General Settings</h2>
              <div class="mt-6 grid w-full grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                  <label for="site-name" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Site Name</label>
                  <div class="mt-2">
                    <input type="text" id="site-name" v-model="settings.siteName" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
                <div>
                  <label for="admin-email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Admin Email</label>
                  <div class="mt-2">
                    <input type="email" id="admin-email" v-model="settings.adminEmail" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Appearance Settings -->
          <div class="w-full overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="w-full px-4 py-5 sm:p-6">
              <h2 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Appearance</h2>
              <div class="mt-6 grid w-full grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                  <label for="theme" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Theme</label>
                  <div class="mt-2">
                    <select id="theme" v-model="settings.theme" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6">
                      <option value="light">Light</option>
                      <option value="dark">Dark</option>
                      <option value="system">System</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label for="language" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Language</label>
                  <div class="mt-2">
                    <select id="language" v-model="settings.language" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6">
                      <option value="en">English</option>
                      <option value="es">Spanish</option>
                      <option value="fr">French</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notification Settings -->
          <div class="w-full overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="w-full px-4 py-5 sm:p-6">
              <h2 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Notifications</h2>
              <div class="mt-6 space-y-4">
                <div class="flex items-center">
                  <input type="checkbox" id="email-notifications" v-model="settings.emailNotifications" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700" />
                  <label for="email-notifications" class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Email Notifications</label>
                </div>
                <div class="flex items-center">
                  <input type="checkbox" id="push-notifications" v-model="settings.pushNotifications" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700" />
                  <label for="push-notifications" class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Push Notifications</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end w-full">
            <button type="button" @click="saveSettings" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save Changes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const settings = ref({
  siteName: 'Admin Panel',
  adminEmail: 'admin@example.com',
  theme: 'system',
  language: 'en',
  emailNotifications: true,
  pushNotifications: false
})

const saveSettings = () => {
  // TODO: Implement settings save logic
  console.log('Saving settings:', settings.value)
}
</script>

<style scoped>
/* Remove the settings-view class as it's no longer needed */
</style> 