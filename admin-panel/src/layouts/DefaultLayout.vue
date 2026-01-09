<script setup lang="ts">
import { ref } from 'vue'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'

const isSidebarOpen = ref(true)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

// Close sidebar on mobile
const closeSidebar = () => {
  if (window.innerWidth < 1024) {
    isSidebarOpen.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <Header @toggle-sidebar="toggleSidebar" />
    
    <!-- Sidebar -->
    <Sidebar :is-open="isSidebarOpen" @toggle="closeSidebar" />
    
    <!-- Main Content -->
    <main
      :class="[
        'transition-all duration-300 ease-in-out pt-16',
        isSidebarOpen ? 'lg:ml-64' : 'lg:ml-0'
      ]"
    >
      <RouterView />
    </main>
  </div>
</template>