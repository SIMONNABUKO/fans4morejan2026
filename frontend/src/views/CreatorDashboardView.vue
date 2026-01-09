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
              Creator Dashboard
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Manage your content, subscribers, and earnings
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Stats (Optional) -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Today's Earnings</div>
            <div class="text-lg font-bold text-primary-light dark:text-primary-dark">$0.00</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Dashboard Menu Sections -->
    <div class="space-y-8">
      <!-- Content Management Section -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-gallery-line text-blue-600 dark:text-blue-400 text-lg"></i>
          </div>
          <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">Content Management</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <DashboardMenuItem
            v-for="item in contentManagementItems"
            :key="item.id"
            :icon="item.icon"
            :label="t(item.label)"
            :beta="item.beta"
            :to="item.to"
          />
        </div>
      </div>

      <!-- Business Tools Section -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-briefcase-line text-green-600 dark:text-green-400 text-lg"></i>
          </div>
          <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">Business Tools</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <DashboardMenuItem
            v-for="item in businessToolsItems"
            :key="item.id"
            :icon="item.icon"
            :label="t(item.label)"
            :beta="item.beta"
            :to="item.to"
          />
        </div>
      </div>

      <!-- Analytics & Insights Section -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-bar-chart-line text-purple-600 dark:text-purple-400 text-lg"></i>
          </div>
          <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">Analytics & Insights</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <DashboardMenuItem
            v-for="item in analyticsItems"
            :key="item.id"
            :icon="item.icon"
            :label="t(item.label)"
            :beta="item.beta"
            :to="item.to"
          />
        </div>
      </div>

      <!-- Financial Management Section -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-money-dollar-circle-line text-yellow-600 dark:text-yellow-400 text-lg"></i>
          </div>
          <h2 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary">Financial Management</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <DashboardMenuItem
            v-for="item in financialItems"
            :key="item.id"
            :icon="item.icon"
            :label="t(item.label)"
            :beta="item.beta"
            :to="item.to"
          />
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import DashboardMenuItem from '@/components/dashboard/DashboardMenuItem.vue'

const { t } = useI18n()

const menuItems = [
  {
    id: 'plans',
    icon: 'ri-vip-crown-line',
    label: 'plans_promos',
    to: '/dashboard/plans'
  },
  {
    id: 'subscribers',
    icon: 'ri-user-star-line',
    label: 'subscribers',
    to: '/dashboard/subscribers'
  },
  {
    id: 'management',
    icon: 'ri-settings-line',
    label: 'management',
    beta: true,
    to: '/dashboard/management'
  },
  {
    id: 'uploads',
    icon: 'ri-upload-2-line',
    label: 'uploads',
    to: '/dashboard/uploads'
  },
  {
    id: 'lists',
    icon: 'ri-list-check',
    label: 'lists',
    to: '/dashboard/lists'
  },
  {
    id: 'vault',
    icon: 'ri-safe-line',
    label: 'vault',
    to: '/dashboard/vault'
  },
  {
    id: 'messages',
    icon: 'ri-message-3-line',
    label: 'messages',
    to: '/dashboard/messages'
  },
  {
    id: 'statistics',
    icon: 'ri-bar-chart-line',
    label: 'profile_statistics',
    to: '/dashboard/statistics'
  },
  {
    id: 'earnings',
    icon: 'ri-money-dollar-circle-line',
    label: 'earnings',
    to: '/dashboard/earnings'
  },
  {
    id: 'supporters',
    icon: 'ri-team-line',
    label: 'top_fans',
    to: '/dashboard/supporters'
  },
  {
    id: 'tax',
    icon: 'ri-bank-line',
    label: 'tax_documentation',
    to: '/dashboard/tax'
  }
]

// Organized menu sections
const contentManagementItems = computed(() => 
  menuItems.filter(item => ['uploads', 'vault', 'messages'].includes(item.id))
)

const businessToolsItems = computed(() => 
  menuItems.filter(item => ['plans', 'subscribers', 'management'].includes(item.id))
)

const analyticsItems = computed(() => 
  menuItems.filter(item => ['statistics', 'supporters'].includes(item.id))
)

const financialItems = computed(() => 
  menuItems.filter(item => ['earnings', 'tax'].includes(item.id))
)
</script>
