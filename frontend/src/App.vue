<template>
  <MainLayout>
    <router-view></router-view>
  </MainLayout>
</template>

<script setup>
import MainLayout from './layouts/MainLayout.vue';
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useTrackingLink } from '@/composables/useTrackingLink'

onMounted(() => {
  const { initTrackingLink } = useTrackingLink()
  
  initTrackingLink()
})
</script>

