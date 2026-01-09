<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ t('settings') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your account preferences and security
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Actions -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Account Status</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">Active</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Security Notice -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg mb-8">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
          <i class="ri-shield-check-line text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold mb-2">Security Notice</h3>
          <p class="text-blue-100 leading-relaxed">{{ t('security_notice') }}</p>
        </div>
      </div>
    </div>

    <!-- Settings Menu -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="item in menuItems"
        :key="item.id"
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-200 hover:scale-105"
      >
        <DashboardMenuItem
          :icon="item.icon"
          :label="t(item.label)"
          :to="item.to"
        />
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import DashboardMenuItem from '@/components/dashboard/DashboardMenuItem.vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const menuItems = [
  {
    id: 'account',
    icon: 'ri-user-settings-line',
    label: 'account',
    to: '/settings/account'
  },
  {
    id: 'privacy',
    icon: 'ri-shield-keyhole-line',
    label: 'privacy_protection',
    to: '/settings/privacy'
  },
  {
    id: 'sessions',
    icon: 'ri-shield-check-line',
    label: 'management_sessions',
    to: '/settings/sessions'
  },
  {
    id: 'payments',
    icon: 'ri-bank-card-line',
    label: 'payments',
    to: '/settings/payments'
  },
  {
    id: 'display',
    icon: 'ri-palette-line',
    label: 'display',
    to: '/settings/display'
  },
  {
    id: 'notifications',
    icon: 'ri-notification-3-line',
    label: 'notifications',
    to: '/settings/notifications'
  },
  {
    id: 'about',
    icon: 'ri-information-line',
    label: 'about',
    to: '/settings/about'
  }
]
</script>