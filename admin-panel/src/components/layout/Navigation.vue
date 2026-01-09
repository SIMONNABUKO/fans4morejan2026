<template>
  <div class="sidebar-container">
    <!-- Main navigation items -->
    <nav class="space-y-1 px-2 py-4">
      <!-- Dashboards item -->
      <RouterLink
        to="/dashboards"
        :class="[
          isActive('/dashboards')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <HomeIcon
            :class="[
              isActive('/dashboards')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Dashboards</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- PAGES section header -->
      <div class="mt-6 mb-2 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
        PAGES
      </div>

      <!-- Users item -->
      <RouterLink
        to="/users"
        :class="[
          isActive('/users')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <UsersIcon
            :class="[
              isActive('/users')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Users</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- User Profile item with badge -->
      <RouterLink
        to="/user-profile"
        :class="[
          isActive('/user-profile')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <UserIcon
            :class="[
              isActive('/user-profile')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>User Profile</span>
        </div>
        <div class="flex items-center">
          <span class="bg-blue-500 text-white text-xs font-medium rounded-full h-5 w-5 flex items-center justify-center mr-2">
            5
          </span>
          <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </div>
      </RouterLink>

      <!-- Account item -->
      <RouterLink
        to="/account"
        :class="[
          isActive('/account')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <IdentificationIcon
            :class="[
              isActive('/account')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Account</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- E-commerce item -->
      <RouterLink
        to="/e-commerce"
        :class="[
          isActive('/e-commerce')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <ShoppingCartIcon
            :class="[
              isActive('/e-commerce')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>E-commerce</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- Projects item -->
      <RouterLink
        to="/projects"
        :class="[
          isActive('/projects')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <DocumentChartBarIcon
            :class="[
              isActive('/projects')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Projects</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- Project item -->
      <RouterLink
        to="/project"
        :class="[
          isActive('/project')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <FolderIcon
            :class="[
              isActive('/project')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Project</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- Authentication item -->
      <RouterLink
        to="/authentication"
        :class="[
          isActive('/authentication')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <ShieldCheckIcon
            :class="[
              isActive('/authentication')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Authentication</span>
        </div>
        <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </RouterLink>

      <!-- API Keys item -->
      <RouterLink
        to="/api-keys"
        :class="[
          isActive('/api-keys')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <KeyIcon
            :class="[
              isActive('/api-keys')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>API Keys</span>
        </div>
      </RouterLink>

      <!-- Welcome Page item -->
      <RouterLink
        to="/welcome"
        :class="[
          isActive('/welcome')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <EyeIcon
            :class="[
              isActive('/welcome')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Welcome Page</span>
        </div>
      </RouterLink>

      <!-- Landing Page item with New badge -->
      <RouterLink
        to="/landing"
        :class="[
          isActive('/landing')
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white',
          'group flex items-center justify-between rounded-md px-2 py-2 text-sm font-medium'
        ]"
      >
        <div class="flex items-center">
          <CubeIcon
            :class="[
              isActive('/landing')
                ? 'text-blue-600 dark:text-blue-400'
                : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-300',
              'mr-3 h-5 w-5 flex-shrink-0'
            ]"
            aria-hidden="true"
          />
          <span>Landing Page</span>
        </div>
        <span class="bg-teal-500 text-white text-xs font-medium rounded-full px-2 py-0.5">
          New
        </span>
      </RouterLink>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import {
  HomeIcon,
  UsersIcon,
  UserIcon,
  IdentificationIcon,
  ShoppingCartIcon,
  DocumentChartBarIcon,
  FolderIcon,
  ShieldCheckIcon,
  KeyIcon,
  EyeIcon,
  CubeIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()

const isActive = (path: string) => {
  const currentPath = route.path
  if (path === '/') {
    return currentPath === path
  }
  return currentPath.startsWith(path)
}
</script>

<style scoped>
.sidebar-container {
  height: 100%;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.sidebar-container::-webkit-scrollbar {
  width: 4px;
}

.sidebar-container::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-container::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 4px;
}

.sidebar-container::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}
</style>