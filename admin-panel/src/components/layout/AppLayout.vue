<template>
  <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 transform bg-white shadow-lg transition-transform duration-200 ease-in-out dark:bg-gray-800 lg:static lg:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex h-16 items-center justify-between px-4">
        <RouterLink to="/" class="flex items-center space-x-2">
          <img src="@/assets/logo.svg" alt="Logo" class="h-8 w-8" />
          <span class="text-xl font-bold text-gray-900 dark:text-white">Admin</span>
        </RouterLink>
        <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700 lg:hidden dark:text-gray-400 dark:hover:text-gray-300">
          <XMarkIcon v-if="isSidebarOpen" class="h-6 w-6" />
          <Bars3Icon v-else class="h-6 w-6" />
        </button>
      </div>
      <slot name="sidebar" />
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col w-0 lg:w-full">
      <header class="flex h-16 items-center justify-between border-b bg-white px-4 dark:border-gray-700 dark:bg-gray-800">
        <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700 lg:hidden dark:text-gray-400 dark:hover:text-gray-300">
          <Bars3Icon class="h-6 w-6" />
        </button>
        <div class="flex items-center space-x-4">
          <slot name="header-actions" />
        </div>
      </header>

      <main class="flex-1 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const isSidebarOpen = ref(false)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script> 