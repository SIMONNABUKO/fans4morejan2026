<template>
  <div class="min-h-screen pt-16 transition-all duration-300 lg:pl-64">
    <main class="flex-1">
      <div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900">
        <div class="p-6 lg:p-8">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-8">About</h1>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <p class="text-gray-600 dark:text-gray-300">This is an about page</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
// About page logic here
</script>

<style>
@media (min-width: 1024px) {
  .about {
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
}
</style>
