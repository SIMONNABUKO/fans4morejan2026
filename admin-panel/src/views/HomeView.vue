<script setup lang="ts">
import TheWelcome from '../components/TheWelcome.vue'
</script>

<template>
  <div class="min-h-screen pt-16 transition-all duration-300 lg:pl-64">
    <main class="flex-1">
      <div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
        <div class="p-6 lg:p-8">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-8">Dashboard</h1>
          <TheWelcome />
        </div>
      </div>
    </main>
  </div>
</template>
