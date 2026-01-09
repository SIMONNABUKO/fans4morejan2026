<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { clickOutside } from '@/directives/clickOutside'
import { useAuthStore } from '@/stores/auth'
import { HomeIcon, UsersIcon, DocumentCheckIcon, DocumentTextIcon, PhotoIcon, CurrencyDollarIcon, Cog6ToothIcon, UserGroupIcon, ChatBubbleLeftIcon } from '@heroicons/vue/24/outline'

declare const window: Window & typeof globalThis

const route = useRoute()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const isUserMenuOpen = ref(false)
const isMobileView = ref(false)

// Computed property for showing overlay
const showMobileOverlay = computed(() => {
  return isSidebarOpen.value && isMobileView.value
})

onMounted(() => {
  // Initialize mobile view and sidebar state
  isMobileView.value = window.innerWidth < 1024
  isSidebarOpen.value = !isMobileView.value

  // Add window resize listener
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  // Remove window resize listener
  window.removeEventListener('resize', handleResize)
})

const handleResize = () => {
  // Update mobile view state
  isMobileView.value = window.innerWidth < 1024
  
  // Keep sidebar open by default on desktop, closed on mobile
  if (!isMobileView.value) {
    isSidebarOpen.value = true
  } else {
    isSidebarOpen.value = false
  }
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const closeUserMenu = () => {
  isUserMenuOpen.value = false
}

const handleLogout = async () => {
  await authStore.logout()
}

// Close sidebar when clicking overlay on mobile
const handleOverlayClick = () => {
  if (isMobileView.value) {
    isSidebarOpen.value = false
  }
}

// Close sidebar when navigating on mobile
const handleNavigation = () => {
  if (isMobileView.value) {
    isSidebarOpen.value = false
  }
}

const navigation = [
  { name: 'Dashboard', href: '/', icon: HomeIcon },
  { name: 'Users', href: '/users', icon: UsersIcon },
  {
    name: 'Creator',
    icon: UserGroupIcon,
    items: [
      {
        name: 'Applications',
        to: '/creator/applications'
      },
      {
        name: 'Tier Management',
        to: '/creator/tiers'
      }
    ]
  },
  { name: 'Content', href: '/content', icon: DocumentTextIcon },
  { name: 'Media', href: '/media', icon: PhotoIcon },
  {
    name: 'Financial',
    icon: CurrencyDollarIcon,
    isDropdown: true,
    children: [
      {
        name: 'Platform Wallet',
        href: '/financial/platform-wallet'
      },
      {
        name: 'Transactions',
        href: '/financial/transactions'
      }
    ]
  },
  { name: 'Settings', href: '/settings', icon: Cog6ToothIcon },
  {
    name: 'Communication',
    items: [
      {
        name: 'Messages',
        icon: ChatBubbleLeftIcon,
        to: '/messages',
        active: route.path.startsWith('/messages')
      }
    ]
  }
]
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Overlay for mobile -->
    <div
      v-if="showMobileOverlay"
      class="fixed inset-0 bg-gray-900/50 lg:hidden z-40"
      @click="handleOverlayClick"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 lg:translate-x-0"
      :class="{ '-translate-x-full': !isSidebarOpen }"
    >
      <div class="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Admin Panel</h1>
        <button
          @click="toggleSidebar"
          class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <nav class="mt-4 px-2">
        <div v-for="item in navigation" :key="item.name">
          <!-- Regular menu item -->
          <RouterLink
            v-if="!item.isDropdown && !item.items"
            :to="item.href || '/'"
            class="flex items-center px-4 py-2 mt-2 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            active-class="bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300"
            @click="handleNavigation"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3" />
            <span>{{ item.name }}</span>
          </RouterLink>

          <!-- Menu with sub-items -->
          <div v-else-if="item.items" class="mt-2">
            <div class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200">
              <component :is="item.icon" class="w-5 h-5 mr-3" />
              <span>{{ item.name }}</span>
            </div>
            <RouterLink
              v-for="subItem in item.items"
              :key="subItem.name"
              :to="subItem.to"
              class="flex items-center px-4 py-2 pl-12 mt-1 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
              active-class="bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300"
              @click="handleNavigation"
            >
              {{ subItem.name }}
            </RouterLink>
          </div>

          <!-- Dropdown menu item -->
          <div v-else class="mt-2">
            <div class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200">
              <component :is="item.icon" class="w-5 h-5 mr-3" />
              <span>{{ item.name }}</span>
            </div>
            <RouterLink
              v-for="child in item.children"
              :key="child.name"
              :to="child.href"
              class="flex items-center px-4 py-2 pl-12 mt-1 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
              active-class="bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300"
              @click="handleNavigation"
            >
              {{ child.name }}
            </RouterLink>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Top bar -->
    <header class="fixed top-0 right-0 left-0 z-30 h-16 flex items-center px-4 bg-white dark:bg-gray-800 shadow-sm transition-all duration-300 lg:left-64">
      <button
        @click="toggleSidebar"
        class="p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white lg:hidden"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      
      <!-- Spacer to push user menu to the right -->
      <div class="flex-grow"></div>

      <div class="relative" v-click-outside="closeUserMenu">
        <button
          @click="toggleUserMenu"
          class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <img
            :src="authStore.user?.avatar || 'https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff'"
            alt="User avatar"
            class="w-8 h-8 rounded-full"
          />
          <span class="hidden sm:inline text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ authStore.user?.name || 'Admin' }}
          </span>
          <!-- Dropdown arrow -->
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- User menu dropdown -->
        <div
          v-if="isUserMenuOpen"
          class="absolute right-0 top-full mt-1 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5"
        >
          <div class="py-1">
            <button
              @click="handleLogout"
              class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>Logout</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <div class="pt-16 transition-all duration-300 lg:pl-64 w-full">
      <main class="w-full">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style>
:root {
  --color-primary: 99, 102, 241;
  --color-primary-dark: 79, 70, 229;
}

.bg-primary {
  background-color: rgb(var(--color-primary));
}

.bg-primary-dark {
  background-color: rgb(var(--color-primary-dark));
}

.text-primary {
  color: rgb(var(--color-primary));
}

.text-primary-700 {
  color: rgb(var(--color-primary-dark));
}

.bg-primary-50 {
  background-color: rgba(var(--color-primary), 0.1);
}

.bg-primary-900\/50 {
  background-color: rgba(var(--color-primary-dark), 0.2);
}

.text-primary-300 {
  color: rgba(var(--color-primary), 0.8);
}

.focus\:ring-primary:focus {
  --tw-ring-color: rgb(var(--color-primary));
}

.hover\:bg-primary-dark:hover {
  background-color: rgb(var(--color-primary-dark));
}
</style>