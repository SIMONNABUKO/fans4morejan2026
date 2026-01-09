<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
  <!-- Header -->
  <header class="sticky top-0 z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
    <div class="px-4 py-4 flex items-center gap-3">
      <router-link 
        to="/" 
        class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
      >
        <i class="ri-arrow-left-line text-xl"></i>
      </router-link>
      <h1 class="text-xl font-semibold">
        Earnings/Wallet
      </h1>
    </div>

    <!-- Tabs -->
    <div class="px-4">
      <nav class="flex gap-6 -mb-px">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="pb-3 relative"
          :class="[
            activeTab === tab.id 
              ? 'text-primary-light dark:text-primary-dark' 
              : 'text-text-light-secondary dark:text-text-dark-secondary'
          ]"
        >
          <div class="flex items-center gap-2">
            <i :class="tab.icon"></i>
            {{ tab.label }}
          </div>
          <div 
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-light dark:bg-primary-dark"
          ></div>
        </button>
      </nav>
    </div>
  </header>

  <!-- Content -->
  <div class="p-4 space-y-6">
    <!-- Wallet Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-medium">Wallet</h2>
        <button class="text-text-light-secondary dark:text-text-dark-secondary">
          <i class="ri-refresh-line text-xl"></i>
        </button>
      </div>

      <div class="space-y-3">
        <div class="flex justify-between items-center">
          <span class="text-text-light-secondary dark:text-text-dark-secondary">Total balance</span>
          <span class="text-lg font-medium">$97.82</span>
        </div>
        
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span class="text-text-light-secondary dark:text-text-dark-secondary">Pending balance</span>
            <i class="ri-information-line text-text-light-secondary dark:text-text-dark-secondary"></i>
          </div>
          <span class="text-lg font-medium">$68.97</span>
        </div>

        <div class="flex justify-between items-center">
          <span class="text-text-light-secondary dark:text-text-dark-secondary">Available for Payout</span>
          <span class="text-lg font-medium">$28.85</span>
        </div>

        <button class="w-full py-2 bg-primary-light dark:bg-primary-dark text-white rounded-md hover:opacity-90">
          Request Payout
        </button>
      </div>
    </div>

    <!-- Payout Methods -->
    <div class="space-y-4">
      <h2 class="text-base font-medium">Payout Methods</h2>
      
      <!-- Existing Method -->
      <div class="flex items-center justify-between p-4 bg-surface-light dark:bg-surface-dark rounded-lg">
        <div class="flex items-center gap-3">
          <i class="ri-bank-card-line text-2xl text-purple-500"></i>
          <span>COSMO ending in 8290</span>
        </div>
        <button class="text-red-500 hover:text-red-600">
          <i class="ri-delete-bin-line text-xl"></i>
        </button>
      </div>

      <!-- Add Method Button -->
      <button class="w-full p-4 bg-surface-light dark:bg-surface-dark rounded-lg text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light/80 dark:hover:bg-surface-dark/80">
        Add Payout Method
      </button>
    </div>

    <!-- Payout Requests -->
    <div class="space-y-4">
      <h2 class="text-base font-medium">Payout Requests</h2>
      
      <div class="space-y-2">
        <div v-for="request in payoutRequests" :key="request.id" class="flex items-center justify-between p-4 bg-surface-light dark:bg-surface-dark rounded-lg">
          <div class="space-y-1">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
              {{ request.date }}
            </div>
            <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              Payout #{{ request.id }}
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-accent-success">${{ request.amount }}</span>
            <span class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              {{ request.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('wallet')

const tabs = [
  {
    id: 'wallet',
    label: 'Wallet',
    icon: 'ri-wallet-3-line'
  },
  {
    id: 'statistics',
    label: 'Statistics',
    icon: 'ri-bar-chart-line'
  }
]

const payoutRequests = [
  {
    id: '734516714004834305',
    date: 'Sunday, Jan 12, 2025 at 3:08 AM',
    amount: '34.53',
    status: 'Processed'
  },
  {
    id: '732716992686993408',
    date: 'Monday, Jan 6, 2025 at 9:21 PM',
    amount: '74',
    status: 'Processed'
  }
]
</script>