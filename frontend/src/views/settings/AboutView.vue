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
              {{ t('about') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Learn more about Fans4More
            </p>
          </div>
        </div>
        
        <!-- Right Side: App Version -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Version</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">1.0.0</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- App Information Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
              <i class="ri-heart-line text-white text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fans4More</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              The ultimate platform for content creators and their fans
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-code-s-slash-line text-blue-600 dark:text-blue-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Version</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">1.0.0</span>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-calendar-line text-green-600 dark:text-green-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Build Date</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ getBuildDate() }}</span>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-global-line text-purple-600 dark:text-purple-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Environment</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ getEnvironment() }}</span>
            </div>
          </div>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-user-line text-orange-600 dark:text-orange-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">User ID</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ getUserID() }}</span>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-time-line text-red-600 dark:text-red-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Session Time</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ getSessionTime() }}</span>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-device-line text-indigo-600 dark:text-indigo-400 text-sm"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Platform</span>
              </div>
              <span class="text-sm text-gray-600 dark:text-gray-400">{{ getPlatform() }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-star-line text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Key Features</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Discover what makes Fans4More special
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-camera-line text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Content Creation</span>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-money-dollar-circle-line text-green-600 dark:text-green-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Monetization</span>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-message-3-line text-purple-600 dark:text-purple-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Direct Messaging</span>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-shield-check-line text-orange-600 dark:text-orange-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Privacy & Security</span>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-heart-line text-red-600 dark:text-red-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Fan Engagement</span>
          </div>
          
          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-xl">
            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-bar-chart-line text-indigo-600 dark:text-indigo-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Analytics</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Support & Contact -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-customer-service-2-line text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Support & Contact</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Get help and stay connected
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-question-line text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Help Center</span>
          </button>
          
          <button class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-mail-line text-green-600 dark:text-green-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Contact Support</span>
          </button>
          
          <button class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-file-text-line text-purple-600 dark:text-purple-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Terms of Service</span>
          </button>
          
          <button class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-shield-user-line text-orange-600 dark:text-orange-400 text-sm"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Privacy Policy</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const getBuildDate = () => {
  return new Date().toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const getEnvironment = () => {
  return import.meta.env.MODE === 'production' ? 'Production' : 'Development'
}

const getUserID = () => {
  // This would typically come from your auth store
  return 'User-' + Math.random().toString(36).substr(2, 9)
}

const getSessionTime = () => {
  // This would typically be calculated from session start time
  const hours = Math.floor(Math.random() * 24)
  const minutes = Math.floor(Math.random() * 60)
  return `${hours}h ${minutes}m`
}

const getPlatform = () => {
  const userAgent = navigator.userAgent
  if (userAgent.includes('Mobile')) return 'Mobile'
  if (userAgent.includes('Tablet')) return 'Tablet'
  return 'Desktop'
}
</script>

<style scoped>
/* Add your styles here */
</style>
